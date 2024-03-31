# kw_groups

![Build Status](https://github.com/alex-kalanis/kw_groups/actions/workflows/code_checks.yml/badge.svg)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/alex-kalanis/kw_groups/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/alex-kalanis/kw_groups/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/alex-kalanis/kw_groups/v/stable.svg?v=1)](https://packagist.org/packages/alex-kalanis/kw_groups)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.4-8892BF.svg)](https://php.net/)
[![Downloads](https://img.shields.io/packagist/dt/alex-kalanis/kw_groups.svg?v1)](https://packagist.org/packages/alex-kalanis/kw_groups)
[![License](https://poser.pugx.org/alex-kalanis/kw_groups/license.svg?v=1)](https://packagist.org/packages/alex-kalanis/kw_groups)
[![Code Coverage](https://scrutinizer-ci.com/g/alex-kalanis/kw_groups/badges/coverage.png?b=master&v=1)](https://scrutinizer-ci.com/g/alex-kalanis/kw_groups/?branch=master)

Groups inside the kw_* project.

## PHP Installation

```bash
composer.phar require alex-kalanis/kw_groups
```

(Refer to [Composer Documentation](https://github.com/composer/composer/blob/master/doc/00-intro.md#introduction) if you are not
familiar with composer)


## PHP Usage

1.) Use your autoloader (if not already done via Composer autoloader)

2.) Add some external packages with connection to the local or remote services.

3.) Connect the "kalanis\kw_groups\Processor\Basic" into your app. Extends it for setting your case.

4.) Extend your libraries by interfaces inside the package.

5.) Just call method canAccess() and enjoy.

## Basic Rules

- Can access
  - Group ID equals current one.
  - Group ID is somewhere in the tree of parents.

The group ID is usually string, although it can be integer converted to string before method call.
