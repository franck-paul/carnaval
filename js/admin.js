/*global $, dotclear */
'use strict';

$(() => {
  const carnaval = dotclear.getData('carnaval');
  dotclear.msg.delete_records = carnaval.delete_records;

  $('.checkboxes-helpers').each(function () {
    dotclear.checkboxesHelpers(this);
  });
});
