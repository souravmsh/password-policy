## USER MANUAL

### HOW TO IMPORT
 
### STEPS:- 
1. Create directory packages/vendorName/packageName/src
2. Create ***composer.json*** in vendorName/packageName/
>     {
>     	    "name": "souravmsh/password-policy",
>     	    "description": "Easy password policy manage and password expriry solution",
>     	    "type": "library",
>     	    "license": "MIT",
>     	    "authors": [
>     		{
>     		    "name": "Md. Shohrab Hossain",
>     		    "email": "sourav.diubd@gmail.com"
>     		}
>     	    ],
>     	    "minimum-stability": "dev",
>     	    "require": {
>     		"laravelcollective/html":"v6.x"
>     	    },
>     	    "autoload": {
>     		"psr-4": {
>     		    "Souravmsh\\PasswordPolicy\\": "src/"
>     		}
>     	    },
>     	    "extra": {
>     	      "laravel": {
>     		  "providers": [
>     		      "Souravmsh\\PasswordPolicy\\PackageServiceProvider"
>     		  ]
>     	      }
>     	    }
>     	}

# Browse 
Add route ```/password-policy``` to you application  

3. Create PackageServiceProvider.php - Write config,controller,route,views and other logic
4. Create config, http, views, route.php and other files
5. Add package repositories to root/composer.json file
>     "repositories": [ 
>     	{
>     		"type": "path",
>     		"url": "./packages/souravmsh/password-policy",
>     		"symlink": false
>     	} 
>     ]

6. install package via comopser
> composer require souravmsh/password-policy:dev-master

7. You can publish app 
> - php artisan vendor:publish
> - select your desire package 


##### DOC REFERENCE 
######  https://www.amitmerchant.com/how-to-pull-local-package-laravel-project/

---Enjoy---



### HOW TO USE

##### PASSWORD VALIDATION 

> use Souravmsh\PasswordPolicy\Http\Traits\PasswordPolicy;

Use trait in class
> use PasswordPolicy;

Default fields - password, old_password, password_confirmation
> $this->passwordValidate();

Custom fields
> $this->passwordValidate('password_custom_name',
> 'old_password_custom_name');

Combine password rules with other existing rules
>     $this->validate($request, $this->passwordValidate('password', '', '', [
>         'name'  => 'required',
>         'email' => 'required',
>     ]));


