/*global $, dotclear */
'use strict';

$(() => {
  $('.checkboxes-helpers').each(function () {
    dotclear.checkboxesHelpers(this);
  });
});