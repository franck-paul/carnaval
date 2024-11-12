/*global dotclear */
'use strict';

dotclear.ready(() => {
  const ctrl = document.getElementById('carnaval-control');
  const add = document.getElementById('add-css');

  ctrl.style.display = 'inline';
  add.style.display = 'none';

  ctrl.addEventListener('click', () => {
    add.style.display = '';
    ctrl.style.display = 'none';
    return false;
  });
});
