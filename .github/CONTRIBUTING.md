Contributing
============

Changelog
---------

 * every time new feature is added, new line on CHANGELOG file must be present
   in the form

   > feature - feature description
   

Choose the right branch
-----------------------

Before open your pull request, you must determine on which branch you need to
work.

 * if it contains a bug fix, refactoring or simply some code improvements must
   be opened against latest minor release branch. If latest stable version is
   `v2.2.3`, pull request must be opened starting from branch `2.2`;

   * every time new version is released, that version must be merged to the
     upper minor branch (if exists) until master branch. This allow to keep all
     version fixed and also the next one;

 * if it contains new features must be opened against master branch;

Coding Standards
----------------

 * every time new class or method is added, `@since` annotation should be
   present. Just mark the minor version. `/** @since version x.y */`;

 * always use `yoda` condition;

 * respect `PSR-2` coding standards;
