# symfony-firestore
Symfony bundle to deal with GCP Firestore database


## PHP Unit Testing

Install pcre2 using Homebew
```bash
brew install pcre2
```

Link pcre2.h file to the include directory of your PHP version

```bash
ln -s /opt/homebrew/opt/pcre2/include/pcre2.h /opt/homebrew/opt/php@8.3/include/php/ext/pcre
```

Install PHP pcov extension

```bash
pecl install pcov
```

Ensure pcov extension is enabled

```bash
php -m | grep pcov
```

Run PHPUnit with code coverage

```bash
./vendor/bin/phpunit --coverage-html report
```