# STOP! Hammer Time.

So you've decided to do some coding for PayPerWin. Great!!! Before you can get started though, we need you to read through this document. First, there are installation instructions for getting you up and running for the first time. Then there are sections dedicated to overall development guidelines, guidelines for backend, and guidelines for frontend development.

# Installation

## Base

- Install [Node](http://nodejs.org/download/)
- Then, either:
  - install Laravel's recommended environment "[Homestead](http://laravel.com/docs/5.0/homestead)" and set it up for this project
  - OR configure a custom local environment with:
    - Nginx/Apache
    - PHP 5.4+
    - MySQL
    - Redis
    - Composer

## Setup

### The basics

Regardless if you chose Homestead or the custom local configuration, in the *project root folder* of the machine **that will serve the app** run these commands.
- `composer install`
- `php artisan migrate`
- `php artisan db:seed`

### Package installation

On local machine only (*not* in Homestead), in the *project root folder* run the following commands, depending on what you have already. If you're not sure, just go for the first option, below. It's safe even if repeated, but slow.

#### You don't have **bower and gulp** installed globally?

- `sh setup` or `./setup`

#### You DO have **bower and gulp** installed globally?

- `sh setup -s` or `./setup -s`

 Both -s and --skip work. This option skips the gulp and bower global installation, and just gets the app's packages.

### Environment

You'll need to add a few details specific to your case in an environment file.
- Check [Laravel's docs](http://laravel.com/docs/5.0/configuration) for the `.env` / `.env.example` config.
- If you plan on using Facebook or Twitch functionality, you'll need to create your own dev apps and credentials at the relevant sites. Add these details to `.env`

## Run

You can now visit the site locally, depending Homestead or Nginx/Apache configuration. The local web address is up to you.

## Watch

To avoid re-running `gulp` after every small change in the resources/assets files, run this command while developing:
- `gulp watch`

# Updating

If you've been developing already and are continuing with a newer version, you'll ideally:
1. Do a clean `git clone` from your updated fork
2. Wipe your database (to avoid migration collisions)
3. Then run the following in your project root folder
  - `composer install`
  - `php artisan migrate`
  - `php artisan db:seed`

  ... and locally (not in Homestead)
  - `sh setup -s` or `./setup -s`

At this point you're good to go. Much faster than the initial preparation. :)

# Development

Now for the juicy bits...

## General Guidelines

### "KISS"

Value simplicity over complexity. That includes using available packages over bespoke code, as long as the packages don't complicate development!

### Meaningful variables and methods

`$a` and `function f($i)` don't say anything. `$author` and `function find($id)` do. Opt for longer, but more expressive names over shorter, but ambiguous ones.

### CamelCase instead of snake_case

This_is_an_ugly_method_name();

### Separation of Concerns

Make sure logically independent parts of your code are in separate classes that aren't explicitly linked. Don't create bloated controllers that do *everything.* At the same time, don't create an absurd number of layers of abstractions. You don't need a factory that makes a gateway that handles a service that calls a repository that uses a model... Balance is key!

### Publishing Assets

All frontend assets are stored in the /resources/assets folder and are published to /public based on the `gulpfile.js` configuration.

Do **NOT** commit anything to the /public folder yourself. Use `gulpfile.js` Elixir commands to copy any files and folders from the /resources/assets, /bower_components, and /vendor directories.

## Backend (Laravel) Guidelines

### Dependency Injection

...is your friend! Don't rely on helper methods and Facades outside of view templates and route files. Use Dependency Injection in method arguments to ease testing and make the code more transparent. Write your own repositories and services with Contracts that are bound in a Service Provider.

### Commenting

### Inheriting documentation

Interface/Parent:

```
/**
 * Create and store an instance of the repository's model.
 *
 * @param array $data
 *
 * @return \Illuminate\Database\Eloquent\Model
 */
public function create(array $data);
```

Implementation/Subclass:

```
/**
 * {@inheritdoc}
 *
 * @return User
 */
public function create(array $data)
{
	//
}
```

### Testing

#### Creating tests

There is a custom artisan command available that will create a namespaced test case for you. Running `php artisan make:test Name/Space/Foo/Bar` will result in the following directory and file structure: `/tests/Name/Space/Foo/Bar.php`, with `Bar.php` having the namespace `AppTests\Name\Space\Foo`.

#### Using Mockery

The Mockery Adapter is included in the `phpunit.xml` config file, meaning `Mockery::close()` is run automatically after every test.

## Frontend (RequireJS) Guidelines
