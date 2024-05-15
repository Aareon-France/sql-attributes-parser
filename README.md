<h1 align="center">
  <br>
    <img src="assets/logo.png" alt="Logo of the project SqlAttributeParser">
  <br>
</h1>

<p align="center">
  This repository provides a way to parse <strong>SQL attributes</strong>, which offers the ability to add metadata on 
    SQL queries.
</p>

<hr>

## Table of contents

* [SQL attributes syntax](#sql-attributes-syntax)
* [Installation](#installation)

## SQL attributes syntax

This parser will allow you to parse SQL attributes that are attached to any SQL statement or expression into an SQL 
query. SQL attributes are unofficial ways to add some metadata on SQL statements and expressions in order to apply 
specific behaviors or automated processes regarding the SQL query the attributes are attached on. In this way,
they acts like PHP Attributes.

### Examples

Attributes are applicable on statements:
```sql
#[StatementAttribute]
CREATE TABLE `myTable`…
```

They are also applicable on expressions:
```sql
SELECT
    #[ExprAttribute]
    `myField`
FROM `myTable`;
```

Attributes can own arguments to vary the specific behaviors the processors of those attributes may have:
```sql
#[Since(version: 8.0.5, dbms_list: 'mysql,pgsql,oracle')]
```
The arguments are mandatory to be named, and their values are always interpreted as strings. To allow characters 
separators like `:` or `,` in your values, you must wrap the value of your argument with simple quotes (`'`) or double
quotes (`"`).

### SQL Attribute definition

The EBNF for the syntax of attributes is:
```ebnf
attribute           = "#[", attribute-name, [ attribute-arguments ], "]";
attribute-name      = uppercase-letter, { letter / digit };
attribute-arguments = "(", { attribute-argument, ",{[ ]}" }, ")";
attribute-argument  = {letter / digit / "_" / "-"}, ":{[ ]}", ["'" / '"'], STRING, ["'" / '"'];
uppercase-letter    = "A" .. "Z";
lowercase-letter    = "a" .. "z";
letter              = [uppercase-letter / lowercase-letter]
digit               = "0" .. "9";
```

## Installation

Via composer, you can add the repository in your composer.json by doing:
```shell
composer require aareon/sql-attributes-parser
```

