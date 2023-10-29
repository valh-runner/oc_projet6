# Projet6 - SnowTricks

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/93c9b6cb48e54f46859d632d11c6b802)](https://app.codacy.com/gh/valh-runner/oc_projet6/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)

Creation of a community site focused on the presentation of snowboard figures via the Symfony framework.

## Environment used during development

-   WampServer 3.1.7
    -   Apache 2.4.37
    -   PHP 8.2.12
    -   MySQL 5.7.24
-   Composer 2.6.5
-   Git 2.24.0
-   Symfony 5.4.29
-   PHPUnit 9.5.28
-   jQuery 3.4.1
-   Bootstrap 4.4.1

## Installation

### Environment setup

It is necessary to dispose of a PHP server and Mysql or MariaDB server installed.\
They must be versions compatible with the project.
You can install them yourself or install a complete environment.
Depending on your operating system, several environments can be installed, including:
-   Windows : WAMP (<http://www.wampserver.com/>)
-   MAC : MAMP (<https://www.mamp.info/en/mamp/>)
-   Linux : LAMP (<https://doc.ubuntu-fr.org/lamp>)
-   Cross system: XAMP (<https://www.apachefriends.org/fr/index.html>)

The project requires PHP 8.2.0 or higher to run.\
Prefer to have MySQL 5.6 or higher.\
Make sure PHP is in the Path environment variable.\
Note that PHP must have the following extensions activated:
-   fileinfo
-   intl
-   curl
-   openssl
-   pdo_mysql
-   mb_string (for slug generation)

You need an installation of Composer (<https://getcomposer.org/>)\
You have to install Symfony CLI as well (<https://symfony.com/download>)\
If you want to use Git (optional), install it. (<https://git-scm.com/downloads>)


### Project files local deployement

Manually download the content of the Github repository to a location on your file system.\
You can also use git.\
In Git, go to the chosen location and execute the following command:
```
git clone https://github.com/valh-runner/oc_projet6.git .

```

Open a command console and join the application root directory.\
Install dependencies by running the following command:
```
symfony composer install
```

### Database generation

Change the database connection values for correct ones in the .env file.\
Like the following example with a snowtricks named database to create:
```
DATABASE_URL=mysql://root:@127.0.0.1:3306/snowtricks?serverVersion=5.7
```

In a new console placed in the root directory of the application;\
Launch the creation of the database:
```
symfony console doctrine:database:create
```

Then, build the database structure using the following command:
```
symfony console doctrine:migrations:migrate
```

Finally, load the initial dataset into the database with or without example users.\
To load only the initial dataset, use the following command:
```
symfony console doctrine:fixtures:load --group=AppFixtures
```
Alternatively, if you want to load the initial dataset and generic users, use this command:
```
symfony console doctrine:fixtures:load
```

### Configure the mailer connection address

Go to the .env file in the project root and find the next line:
```
MAILER_DSN=smtp://localhost
```
Then replace it by your own connection string:
```
MAILER_DSN=smtp://user:pass@smtp.example.com
```
For more info, see <https://symfony.com/doc/current/mailer.html#transport-setup>

### Run the web application

#### With Symfony Local Web Server

The first time, get a SSL certificate to can request as https:
```
symfony server:ca:install
```
Then launch the Apache/Php runtime environment with Symfony CLI via the following command:
```
symfony serve -d
```
Now consult <https://localhost:8000> on your browser. Here you go!

#### With a virtualhost

Don't wan't to use Symfony CLI? you can use your favorite Apache/Php/Mysql environment.\
Configure your virtualhost in which the project take place.\
Run it then check your browser as usual.

### Users accounts

Please note the password for each generated generic user is the username of the sample user.\
Get a view in your database to know who's in, you can register yourself too.
Same thing for the password of the admin account, who's named "generator".

### Troubleshooting

If some disfunctionments appear or a file is missing, check your anti-virus quarantine.\
Prefer to set in your anti-virus, an exclusion for the project folder.
