# Changelog

## 10.0 — 2026-11-08

- Use typed getter/setter Cursor method, DC 2.40+
- Enforces some verification before registering module

## 9.0 — 2026-08-03

- Avoid using magic _get(), _set() methods of MetaRecord (2.39+)
- Avoid using magic methods for user preferences and blog settings (2.39+)
- Forgot these ones
- Use MetaRecord::strField(), MetaRecord::intField() and MetaRecord::boolField() whenever is possible

## 8.0 — 2026-04-29

- Use new MetaRecord::cardinal() method
- Harmonize usage of App::blogWorkspace()::NS_... and App::userWorkspace()::WS_... (and use short version of constant name for types)

## 7.3 — 2026-04-13

- DA -&gt; DC

## 7.2 — 2025-11-28

- PHP 8.5 compliance

## 7.1 — 2025-09-22

- Use backend services rather than static calls

## 7.0 — 2025-09-14

- DC 2.36 compliance

## 6.2 — 2025-08-27

- Avoid storing db handler locally (it may change in 2.36+)

## 6.1 — 2025-05-15

- Avoid inline JS as far as possible

## 6.0 — 2025-04-20

- Use TemplateHelper for template blocks/values DC 2.34+

## 5.6 — 2024-11-12

- Switch from jQuery to Vanilla JS

## 5.5 — 2024-09-13

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 5.4 — 2024-05-18

- CSS for group of buttons
- Switch from PNG to SVG for edit icon, 2.30+

## 5.3 — 2023-11-11

- Use SqlStatement

## 5.2.1 — 2023-11-02

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 5.2 — 2023-10-29

- min js
- Use new Form components

## 5.1 — 2023-10-27

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 5.0 — 2023-10-25

- Use instanced item constants rather than interface ones
- Avoid using magic methods as far as possible
- 2.28 compliance

## 4.0 — 2023-08-23

- Dotclear 2.27 compliance, PHP 8.1+

## 3.2 — 2023-07-12

- Use SVG icon rather than PNG
- use static properties rather than admin dynamic ones

## 3.1.1 — 2023-06-27

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 3.1 — 2023-06-20

- Use dcCore::app()-&gt;adminurl-&gt;redirect() rather than Http::redirect()

## 3.0 — 2023-06-10

- Switch to namespace
- Remove deprecated
- Prepare 2.26

## 2.3 — 2023-02-14

- Minified versions
- Move js/css in folders

## 2.2 — 2022-12-25

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 2.1 — 2022-12-18

- 2.24 compliance

## 2.0 — 2022-11-28

- Avoid notice on new installation
- 2.24 compliance More to do…
- Update default branch in _define.php for alternative repository
- Add alternative repository

## 1.6.1 — 2021-09-08

- Init repo with 1.6.1 release from Osku
