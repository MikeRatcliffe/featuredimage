(function() {
  "use strict";

  var input;

  addEvent(window, 'load', function() {
    var container = document.getElementById('noimgurl');

    if (container) {
      input = container.getElementsByTagName('input')[0];
    } else {
      input = document.getElementById('featuredimage');
    }

    if (input) {
      refreshImage();
      addEvent(input, 'keyup', refreshImage);
      addEvent(input, 'change', refreshImage);
      addEvent(input, 'blur', refreshImage);
      addEvent(input, 'paste', refreshImage);
    }
  });

  function refreshImage() {
    var img = document.getElementById('featured-img');

    if (img) {
      img.parentNode.removeChild(img);
    }

    img = new Image();
    img.id = 'featured-img';
    img.src = input.value;
    img.style.display = img.width && img.height ? '' : 'none';
    input.parentNode.appendChild(img);
  }

  function addEvent(elem, evnt, func) {
    if (elem.addEventListener) {
      elem.addEventListener(evnt, func, false);
    } else if (elem.attachEvent) {
      elem.attachEvent("on" + evnt, func);
    }
  }
})();
