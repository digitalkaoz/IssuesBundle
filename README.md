#IssuesBundle

a Symfony Integration for [digitalkaoz/issues](https://github.com/digitalkaoz/issues)

##Installation

surely we use `composer` for that

```
$ composer require digitalkaoz/issues-bundle
```

enable it in your Kernel

```php
<?php
// app/AppKernel.php

    public function registerBundles()
    {
        $bundles = array(
            //...
            new Rs\IssuesBundle\RsIssuesBundle(),
            //...
        );
    }
```

##Configuration

###Issues Trackers
simply introduce the following structure either in your `config.yml` or (for sensitive data) in your `parameters.yml`

```yaml
    rs_issues:
        github:
            - digitalkaoz/issues                                             # concrete repo
            - phpcr/*                                                        # all user/org repos
            - symfony/[Console|Debug]+$                                      # only symfony/Console and symfony/Debug
            - doctrine/(?!common|lexer)([a-z0-9\.-]+)$                       # all but doctrine/common and doctrine/lexer
        jira:
            - https://jira.com PROJKEY [USER] [PASSWORD]                     # username and password are optional
        gitlab:
            - http://gitlab.com/api/v3/ johnsmith/* TOKEN                    # the repo patterns are the same like for github
            - http://gitlab.com/api/v3/ gitlab/[gitlab\-shell|Testme] TOKEN
            - http://gitlab.com/api/v3/ root/(?!six)([a-zA-Z0-9\.-]+)$ TOKEN
```

###Storage Adapter
if you are using `elasticsearch as a storage` you should import the desired mapping and configure `elastica`

```yaml
#app/config/config.yml
imports:
    - { resource: @RsIssuesBundle/Resources/config/es_mapping.yml }

fos_elastica:
    clients:
        default: { host: localhost, port: 9200 }

```

###Routing

include the routing

```yaml
rs_issues:
    resource: "@RsIssuesBundle/Resources/config/routing.xml"
    prefix:   /issues
```

##Usage

if you are using `elasticsearch` as adapter storage (currently the only supported one) you have two ways to sync
all the repository informations into the storage:

```
$ app/console fos:elastica:populate # preferred way
```

or for every other storage adapter

```
$ app/console issues:sync
```

Then you can visit `http://yourdomain.com/issues` (or whatever you prefixed the routing with)

##Extending

###Implementing a new Storage Adapter

simple implement the `Storage` Interface

```php
<?php
interface Storage
{
    /**
     * remove old issues and projects
     */
    public function cleanup();

    /**
     * save a Project and all its Issues
     *
     * @param Project $project
     */
    public function saveProject(Project $project);

    /**
     * get all imported Projects
     *
     * @return Project[]
     */
    public function getProjects();

    /**
     * get all Issues for the provided Project-Id
     *
     * @param  string  $projectId
     * @return Issue[]
     */
    public function getIssues($projectId);
}
```

afterwards create the service and tag it the default storage:

```xml
<service id="rs_issues.storage.my_storage" class="%rs_issues.storage.my_storage.class%">
    <tag name="rs_issues.storage" />
</service>
```

###Implementing a new Synchronizer

if you implemented a new Tracker (with Projects and Issues) you need to write a new synchronizer.
Simply implement the `Synchronizer` Interface.

```php
<?php
interface Synchronizer
{
    /**
     * synchronizes all Projects and Issues from the Tracker into the Storage
     *
     * @param \Closure $cb
     */
    public function synchronize($cb = null);

    /**
     * set the repositories to synchronize
     *
     * @param array $repos
     */
    public function setRepos(array $repos);
}
```

afterwards create the service and tag it

```xml
<service id="rs_issues.synchronizer.mytracker" class="%rs_issues.synchronizer.mytracker.class%">
    <argument type="service" id="rs_issues.storage" />
    <tag name="rs_issues.synchronizer" />
</service>
```

###Building the Frontend Code

we use `Bootstrap` and `Sass` for Stylesheet processing, and `ReactJs` + `CortexJs` for the Javascript Part.

we provide `precompiled` Files in this Repo, but dont give a guarantee for them to be up2date.
If your willing to fiddle around, follow the next steps

simple include the following deps in your `bower.json`

```json
{
    "dependencies": {
        "bootstrap-sass-official":  "~3.2.0",
        "modernizr":                "~2.8.3",
        "respond":                  "~1.4.2",
        "octicons":                 "~2.1.2"
    }
}
```

and the include the following deps in your `package.json`

```json
{
    "dependencies": {
        "cortexjs":     "^0.6.0",
        "marked":       "^0.3.2",
        "moment":       "^2.8.3",
        "react":        "^0.11.2",
        "reqwest":      "^1.1.2",
        "xss":          "^0.1.12"
    }
}
```

after that you should run both package managers:

```
$ npm install
$ bower install
```

now you should compile everything together:

we wont describe that in detail. for processing sass files its easy todo with gulp oder grunt or even assetic:

* [gulp-ruby-sass](https://www.npmjs.org/package/gulp-ruby-sass)
* [grunt-contrib-sass](https://github.com/gruntjs/grunt-contrib-sass)
* [assetic](http://alexandre-salome.fr/blog/Sass-Compass-Assetic-In-Ten-Minutes)

to process the js you could choose from various tools:

* [reactify](https://www.npmjs.org/package/reactify)
* [grunt-react](https://www.npmjs.org/package/grunt-react)

a sample `bower.json`, `package.json` and `gulpfile.js` is provided in this repo. You should copy them to the root of your project.
