/*global dotclear */
'use strict';

dotclear.ready(() => {
  const carnaval = dotclear.getData('carnaval');

  for (const elt of document.querySelectorAll('.checkboxes-helpers')) {
    dotclear.checkboxesHelpers(elt);
  }

  // Confirm on item removal
  const remove = document.getElementById('removeaction');
  remove?.addEventListener('click', (event) => dotclear.confirm(carnaval.delete_records, event));
});
