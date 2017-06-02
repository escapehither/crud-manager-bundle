.. master-hotel documentation master file, created by
   sphinx-quickstart on Thu May 11 22:18:52 2017.
   You can adapt this file completely to your liking, but it should at least
   contain the root `toctree` directive.

Welcome to master-hotel's documentation!
========================================

Contents:

.. toctree::
   :maxdepth: 3

   chapter01
   chapter02
   chapter03




$project
========

The startkitBundle provide a ready crud (create,edit,update,delete) Action and a security access manager to a resource(entity) in your project.
The aim was to avoid code duplication. When i start a new project i wanted to be only focused on the business logic. **It's not an admin generator**.

Look how easy it is to use:

    import project
    # Get your stuff done
    project.do_stuff()

Features
--------

- Crud ready to use. The crud Manager can handle both html and json request format.
- User Manager
- Security using voters.
- Search Bundle -- Need work.


Installation
------------

Install $project by running:

    install project

Contribute
----------

- Issue Tracker: github.com/$project/$project/issues
- Source Code: github.com/$project/$project

Support
-------

If you are having issues, please let us know.
We have a mailing list located at: project@google-groups.com

License
-------

The project is licensed under the MIT license


Indices and tables
==================

* :ref:`genindex`
* :ref:`modindex`
* :ref:`search`

