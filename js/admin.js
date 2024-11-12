/*global dotclear */
'use strict';

dotclear.ready(() => {
  const carnaval = dotclear.getData('carnaval');
  dotclear.msg.delete_records = carnaval.delete_records;

  for (const elt of document.querySelectorAll('.checkboxes-helpers')) {
    dotclear.checkboxesHelpers(elt);
  }
});
