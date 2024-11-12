/*global $, dotclear */
'use strict';

dotclear.ready(() => {
  const control = $('#carnaval-control');
  const addcss = $('#add-css');
  control.css('display', 'inline');
  addcss.hide();
  control.click(function () {
    addcss.show();
    $(this).hide();
    return false;
  });

  $('#active').change(function () {
    if (this.checked) {
      $('#new-class,#classes-form').show();
    } else {
      $('#new-class,#classes-form').hide();
    }
  });
});
