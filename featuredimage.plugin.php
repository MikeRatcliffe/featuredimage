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

    $general_fieldset = $ui->append('fieldset', 'general_settings', _t('Settings', 'twitter'));
    $general_fieldset->append( 'text', 'noimgurl', 'featuredimage__noimgurl', _t('Empty image URL:', 'plugin_locale') );
    $general_fieldset->append( 'text', 'width', 'featuredimage__width', _t('Image width (default 150):', 'plugin_locale') );
    $general_fieldset->append( 'text', 'height', 'featuredimage__height', _t('Image height (default 150):', 'plugin_locale') );
    $general_fieldset->append( 'text', 'summarylength', 'featuredimage__summarylength', _t('Summary length (default 300 chars):', 'plugin_locale') );
    $general_fieldset->append('submit', 'save', _t('Save', 'plugin_locale'));
    return $ui;
  }

  /**
   * Modify publish form. We're going to add the custom 'featuredimage' field.
   */
  public function action_form_publish($form, $post, $context)
  {
    $form->insert('tags', 'text', 'featuredimage_summary', 'null:null', _t('Please enter the summary'), 'admincontrol_textArea');
    $form->featuredimage_summary->value = $post->info->featuredimage_summary;
    $form->featuredimage_summary->template = 'admincontrol_textarea';
    $form->insert('featuredimage_summary', 'text', 'featuredimage_image', 'null:null', _t('The url of the featured image'), 'admincontrol_textArea');
    $form->featuredimage_image->value = $post->info->featuredimage_image;
    $form->featuredimage_image->template = 'admincontrol_text';
  }

  /**
   * Save our data to the database
   */
  public function action_publish_post( $post, $form )
  {
    $post->info->featuredimage_image = $form->featuredimage_image->value;
    $post->info->featuredimage_summary = $form->featuredimage_summary->value;
  }

  /**
   * Make featuredimage_image available through $post.
   */
  public function filter_post_featuredimage_image($featuredimage_image, $post)
  {
    if ($post->content_type == Post::type('entry')) {
      if (!empty($post->info->featuredimage_image)) {
        return $post->info->featuredimage_image;
      }

      preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->content, $matches);

      if (sizeof($matches[1]) > 0) {
        $img = $matches [1] [0];
      } else {
        $img = Options::get( 'featuredimage__noimgurl' );
      }

      return $img;
    }
    return $featuredimage_image;
  }

  /**
   * Make featuredimage_summary available through $post.
   */
  public function filter_post_featuredimage_summary($featuredimage_summary, $post)
  {
    if ($post->content_type == Post::type('entry')) {
      if (!empty($post->info->featuredimage_summary)) {
        return $post->info->featuredimage_summary;
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
    return $featuredimage_summary;
  }

  /**
   * Make featuredimage_width available through $post.
   */
  public function filter_post_featuredimage_width($featuredimage_width, $post)
  {
    return Options::get('featuredimage__width', 150);
  }

  /**
   * Make featuredimage_height available through $post.
   */
  public function filter_post_featuredimage_height($featuredimage_height, $post)
  {
    return Options::get('featuredimage__height', 150);
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
