## FooBlog Demo Application

This application exists to show how you can use postman/newman to run automated tests and collect code coverage. To get started make sure you have [composer](https://getcomposer.org) installed and update the database config to point to a working database. Then install the app and migrate and seed the database

```shell
    composer install
    ./artisan migrate --seed
```

Next configure your web server to serve the project at api.fooblog.loc and assuming your running this on a local server add the following hosts entry to your [hosts file](http://www.howtogeek.com/howto/27350/beginner-geek-how-to-edit-your-hosts-file/).

```
127.0.0.1  api.fooblog.loc
```

Next use [Postman](https://www.getpostman.com/) and import the collection and environment located in /postman or use [newman](https://www.getpostman.com/docs/newman_intro) and run it on the command line:

```shell
    newman --insecure  -e postman/build.json -c postman/collection.json
```
