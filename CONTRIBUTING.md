Thank you to read and sign the following contributor agreement http://www.akeneo.com/contributor-license-agreement/

Every PR should start with:

```
| Q                    | A
| -------------------- | ---
| Bug fix?             |
| New feature?         |
| Specs pass?*         |
| Checkstyle issues?** |
| PMD issues?***       |
| Fixed tickets        |
| Doc PR               |
```

> *   ./bin/phpspec run -v
> **  ./bin/phpcs --standard=PSR2 --extensions=php --ignore=vendor/,bin/,spec/ .
> *** ./Resources/jenkins/phpmd_akeneo . text --exclude vendor/,bin/,spec/
