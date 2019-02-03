# restcountries.eu supporting class 
---

### Installation

Copy repository to your local folder and run command
```
$ composer install
```
or install through packagist: 
```
$ composer create-project openrun/restcountries --stability dev
```

### Task Description

To create a PHP application that will list all the countries which speaks the same language or 
checks if given two countries speak the same language by using open rest api: 

https://restcountries.eu/ 
 
### Requirements: 

[-] Php application should be executable from console by giving country name as parameter: 
```
$ php index.php Spain 
```

*[Output]*
    
    Country language code: es

    Spain speaks same language with these countries: Uruguay, Bolivia, Argentina.. 
 
[-] In case of two parameters given, application should tell if the countries talking the same 
language or not 

```
$ php index.php Spain England 
```
 
*[Output]* 

```
Spain and England do not speak the same language 
```
