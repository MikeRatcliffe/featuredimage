<?php
error_reporting( E_ALL | E_STRICT );
?>
<?php

/**
* Featured Image - A plugin to display a featured image and summary field to the
* publish form.
* @package featuredimage
**/
class featuredimage extends Plugin
{
  const VERSION= '0.1';

  public function configure()
  {
    $ui = new FormUI( strtolower( get_class( $this ) ) );
    $ui->append( 'text', 'noimgurl', 'featuredimage__noimgurl', _t('Empty image URL:', 'plugin_locale') );
    $ui->append( 'text', 'width', 'featuredimage__width', _t('Image width (default 150):', 'plugin_locale') );
    $ui->append( 'text', 'summarylength', 'featuredimage__summarylength', _t('Summary length (default 300 chars):', 'plugin_locale') );
    $ui->append('submit', 'save', _t('Save', 'plugin_locale'));
    return $ui;
  }

  /**
   * Modify publish form. We're going to add the custom 'featuredimage' field.
   */
  public function action_form_publish($form, $post, $context)
  {
    if ($form->content_type->value == Post::type('entry')) {
      $form->insert('tags', 'text', 'summary', 'null:null',
                    _t('Please enter the summary'), 'admincontrol_textArea');
      $form->summary->value = $post->info->summary;
      $form->summary->template = 'admincontrol_textarea';

      $form->insert('summary', 'text', 'featuredimage', 'null:null',
                    _t('The url of the featured image (free text allows the use ' .
                       'of images from anywhere).'), 'admincontrol_textArea');
      $form->featuredimage->value = $post->info->featuredimage;
      $form->featuredimage->template = 'admincontrol_text';
    }
  }

  /**
   * Save our data to the database
   */
  public function action_publish_post( $post, $form )
  {
    if ($post->content_type == Post::type('entry')) {
      $post->info->featuredimage = $form->featuredimage->value;
      $post->info->summary = $form->summary->value;
    }
  }

  /**
   * Make the featuredimage field available through the post class.
   */
  public function filter_post_featuredimage($featuredimage, $post) {
    if ($post->content_type == Post::type('entry')) {
      if (isset($post->info->featuredimage)) {
        return $post->info->featuredimage;
      }

      preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->content, $matches);
      $img = $matches [1] [0];

      if (empty($img)) { //Defines a default image
        $img = Options::get( 'featuredimage__noimgurl' );
      }
      return $img;
    }
    return $featuredimage;
  }

  /**
   * Make the summary field available through the post class.
   */
  public function filter_post_summary($summary, $post) {
    if ($post->content_type == Post::type('entry')) {
      if (!empty($post->info->summary)) {
        return $post->info->summary;
      }

      $maxSumLen = Options::get( 'featuredimage__summarylength', 300);
      $strippedContent = strip_tags($post->content);
      $trimmedContent = substr($strippedContent, 0, $maxSumLen + 1);

      // If the last character of the trimmed content is non-alphabetic we
      // need to remove it.
      if (preg_match("/[\W]$/", $trimmedContent)) {
        $trimmedContent = substr_replace($trimmedContent ,"", -1);
      }

      return $trimmedContent . '&hellip;';
    }
    return $summary;
  }

  /**
   * Make featuredimage__width available through $post.
   */
  public function filter_post_featuredimage__width($width, $post) {
    return Options::get('featuredimage__width', 150);
  }

  public function action_admin_header($theme)
  {
    if ($theme->page == 'plugins' || ($theme->page == 'publish' && $theme->post->content_type == Post::type('entry'))) {
      Stack::add('admin_stylesheet', array($this->get_url() . '/featuredimage.css', 'screen'));
      Stack::add('admin_header_javascript', $this->get_url() . '/featuredimage.js', 'featuredimage');
    }
  }
}
?>
