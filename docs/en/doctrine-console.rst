Doctrine Console
================

Some Doctrine packages such as ``doctrine/dbal`` and ``doctrine/orm``
provide useful console commands to interact with your database and your
entities:

- `Doctrine DBAL Commands
  <https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/cli-tools.html#cli-tools>`_
- `Doctrine ORM Commands
  <https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/tools.html#doctrine-console>`_

This bundle automatically registers those commands, but it renames them
in the process so as to group them under the ``doctrine:`` namespace.

Note that the bundle also provide some additional commands that are not
part of the aforementioned packages.

Command Name Mapping
--------------------

The following table shows the mapping between the original Doctrine command
names and the names used by this bundle:

+--------------------------------------------+---------------------------------------+
| Bundle Command Name                        | Original Doctrine Command Name        |
+============================================+=======================================+
| ``doctrine:cache:clear-metadata``          | ``orm:clear-cache:metadata``          |
+--------------------------------------------+---------------------------------------+
| ``doctrine:cache:clear-query``             | ``orm:clear-cache:query``             |
+--------------------------------------------+---------------------------------------+
| ``doctrine:cache:clear-result``            | ``orm:clear-cache:result``            |
+--------------------------------------------+---------------------------------------+
| ``doctrine:cache:clear-collection-region`` | ``orm:clear-cache:region:collection`` |
+--------------------------------------------+---------------------------------------+
| ``doctrine:cache:clear-entity-region``     | ``orm:clear-cache:region:entity``     |
+--------------------------------------------+---------------------------------------+
| ``doctrine:cache:clear-query-region``      | ``orm:clear-cache:region:query``      |
+--------------------------------------------+---------------------------------------+
| ``doctrine:schema:create``                 | ``orm:schema-tool:create``            |
+--------------------------------------------+---------------------------------------+
| ``doctrine:schema:drop``                   | ``orm:schema-tool:drop``              |
+--------------------------------------------+---------------------------------------+
| ``doctrine:schema:update``                 | ``orm:schema-tool:update``            |
+--------------------------------------------+---------------------------------------+
| ``doctrine:mapping:info``                  | ``orm:info``                          |
+--------------------------------------------+---------------------------------------+
| ``doctrine:mapping:describe``              | ``orm:mapping:describe``              |
+--------------------------------------------+---------------------------------------+
| ``doctrine:query:dql``                     | ``orm:run-dql``                       |
+--------------------------------------------+---------------------------------------+
| ``doctrine:schema:validate``               | ``orm:validate-schema``               |
+--------------------------------------------+---------------------------------------+

Additionally, the bundle provides the following commands:

- ``doctrine:database:create``
- ``doctrine:database:drop``

To get a list of all available Doctrine commands, run:

.. code-block:: console

    php bin/console list doctrine

It is possible to get help on any specific command. For example,
to get help on the ``doctrine:schema:update`` command, run:

.. code-block:: console

    php bin/console doctrine:schema:update --help

Profiling long-running commands
-------------------------------

With Symfony DoctrineBridge 8.2 or later, Doctrine's debug middleware follows
Symfony's profiler state. Disabling the profiler stops collecting SQL queries,
backtraces and Stopwatch periods. Enabling it again resumes collection, including
for connections and prepared statements created while it was disabled.

This also follows Symfony's default profiler state for console commands. Use
``--profile`` to collect a command profile, or explicitly enable the profiler
when collecting and saving a profile in application code.

Older DoctrineBridge versions, or applications without Symfony's profiler state
checker service, retain the previous collection behavior. For these versions,
disable ``doctrine.dbal.profiling`` in the command's environment, or run the
command with ``--no-debug`` when profiling follows ``kernel.debug``. SQL logging
is configured separately; disabling the profiler does not disable a logger.
