# Simple states and transitions workflow for Laravel Eloquent

[![Latest Version on Packagist](https://img.shields.io/packagist/v/soap/eloquent-workflow.svg?style=flat-square)](https://packagist.org/packages/soap/eloquent-workflow)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/soap/eloquent-workflow/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/soap/eloquent-workflow/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/soap/eloquent-workflow/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/soap/eloquent-workflow/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/soap/eloquent-workflow.svg?style=flat-square)](https://packagist.org/packages/soap/eloquent-workflow)

This package add state transition workflow to eloquent model. User can choose to take action from available transitions, makes eloquent model to transit from one state to another state. It is based on lots of work from [codewiser/workflow](https://github.com/codewiser/workflow)

## Support us


## Installation

You can install the package via composer:

```bash
composer require soap/eloquent-workflow
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="eloquent-workflow-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="eloquent-workflow-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="eloquent-workflow-views"
```

## Usage

* [Setup](#setup)
* [Consistency](#consistency)
* [Authorization](#authorization)
* [Business Logic](#business-logic)
    * [Disabling Transitions](#disabling-transitions)
    * [Removing Transitions](#removing-transitions)
    * [User Provided Data](#additional-context)
* [Translations](#translations)
* [JSON](#json-serialization)
* [Events](#events)
    * [EventListener](#eventlistener)
    * [Callback](#transition-callback)
* [Log Transitions](#transition-history) 

Package provides workflow functionality to Eloquent Models.

Workflow is a sequence of states, document evolve through.
Transitions between states inflicts the evolution road.

### Setup

First, describe the workflow blueprint with available states and transitions:

```php
class ArticleWorkflow extends \Soap\EloquentWorkflow\WorkflowBlueprint
{
    public function states(): array
    {
        return [
            'new',
            'review',
            'published',
            'correction',
        ];
    }
    
    public function transitions(): array
    {
        return [
            ['new', 'review'],
            ['review', 'published'],
            ['review', 'correction'],
            ['correction', 'review']
        ];
    }
}
```

> You may use `Enum` instead of scalar values:

Next, include trait and create method to bind a blueprint to model's attribute.

```php
use \Soap\EloquentWorkflow\Example\Enum;
use \Soap\EloquentWorkflow\Example\ArticleWorkflow;
use \Soap\EloquentWorkflow\StateMachineEngine;

class Article extends Model
{
    use \Soap\EloquentWorkflow\Traits\HasWorkflow;
    
    public function state(): StateMachineEngine
    {
        return $this->workflow(ArticleWorkflow::class, 'state');
    }
}
```

That's it.

### Consistency

Workflow observes Model and keeps state machine consistency healthy.

```php
use \Soap\EloquentWorkflow\Example\Enum;

// creating: will set proper initial state
$article = new \Soap\EloquentWorkflow\Example\Article();
$article->save();
assert($article->state == 'new');

// updating: will examine state machine consistency
$article->state = 'review';
$article->save();
// No exceptions thrown
assert($article->state == 'review');
```

### State and Transition objects

In an example above we describe blueprint with scalar values, but actually they will be transformed to the objects. Those objects bring some additional functionality to the states and transitions, such as caption translations, transit authorization, routing rules etc...

```php
use \Soap\EloquentWorkflow\State;
use \Soap\EloquentWorkflow\Transition;

class ArticleWorkflow extends \Soap\EloquentWorkflow\WorkflowBlueprint
{
    public function states(): array
    {
        return [
            State::make('new'),
            State::make('review'),
            State::make('published'),
            State::make('correction'),
        ];
    }
    
    public function transitions(): array
    {
        return [
            Transition::make('new', 'review'),
            Transition::make('review', 'published'),
            Transition::make('review', 'correction'),
            Transition::make('correction', 'review'),
        ];
    }
}
```

### Authorization

As model's actions are not allowed to any user, as changing state is not allowed to any user. You may define transition authorization rules either using `Policy` or using `callable`.

#### Using Policy

Provide ability name:

```php
use \Soap\EloquentWorkflow\Transition;

Transition::make('new', 'review')
    ->authorizedBy('transit');
```

#### Using Closure

```php
use \Soap\EloquentWorkflow\Transition;
use \Illuminate\Support\Facades\Gate;

Transition::make('new', 'review')
    ->authorizedBy(fn(Article $article) => Gate::allows('transit', $article));
```

#### Authorized Transitions

To get only transitions, authorized to the current user, use `authorized` method of `TransitionCollection`:

```php
$article = new \Soap\EloquentWorkflow\Example\Article();

$transitions = $article->state()
    // Get transitions from model's current state.
    ->transitions()
    // Filter only authorized transitions. 
    ->onlyAuthorized();
```

#### Authorizing Transition

When accepting user request, do not forget to authorize workflow state changing.

```php
public function update(Request $request, \Soap\EloquentWorkflow\Example\Article $article)
{
    $this->authorize('update', $article);
    
    if ($state = $request->input('state')) {
        // Check if user allowed to make this transition
        $article->state()->authorize($state);
    }
    
    $article->fill($request->validated());
    
    $article->save();
}
```

### Business Logic

#### Disabling transitions

Transition may have some prerequisites to a model. If model fits this conditions then the transition is possible.

Prerequisite is a `callable` with `Model` argument. It may throw an exception.

To disable transition, prerequisite should throw a `TransitionRecoverableException`. Leave helping instructions in exception message.

Here is an example of issues user may resolve.

```php
use \Soap\EloquentWorkflow\Transition;
use \Soap\EloquentWorkflow\Exceptions\TransitionRecoverableException;

Transition::make('new', 'review')
    ->before(function(Article $model) {
        if (strlen($model->body) < 1000) {
            throw new TransitionRecoverableException(
                'Your article should contain at least 1000 symbols. Then you may send it to review.'
            );
        }
    })
    ->before(function(Article $model) {
        if ($model->images->count() == 0) {
            throw new TransitionRecoverableException(
                'Your article should contain at least 1 image. Then you may send it to review.'
            );
        }
    });
```

User will see the problematic transitions in a list of available transitions.
User follows instructions to resolve the issue and then may try to perform the transition again.

#### Removing transitions

In some cases workflow routes may divide into branches. Way to go forced by business logic, not user. User even shouldn't know about other ways.

To completely remove transition from a list, prerequisite should throw a `TransitionFatalException`.

```php
use \Soap\EloquentWorkflow\Transition;
use \Soap\EloquentWorkflow\Exceptions\TransitionFatalException;

Transition::make('new', 'to-local-manager')
    ->before(function(Order $model) {
        if ($model->amount >= 1000000) {
            throw new TransitionFatalException("Order amount is too big for this transition.");
        }
    }); 

Transition::make('new', 'to-region-manager')
    ->before(function(Order $model) {
        if ($model->amount < 1000000) {
            throw new TransitionFatalException("Order amount is too small for this transition.");
        }
    }); 
```

User will see only one possible transition depending on order amount value.

#### Additional Context

Sometimes application requires an additional context to perform a transition. For example, it may be a reason the article was rejected by the reviewer.

First, declare validation rules in transition definition:

```php
use \Soap\EloquentWorkflow\Transition;

Transition::make('review', 'reject')
    ->rules([
        'reason' => 'required|string|min:100'
    ]);
```

Next, set the transition context in the controller:

```php
use Illuminate\Http\Request;

public function update(Request $request, \Soap\EloquentWorkflow\Example\Article $article)
{
    $this->authorize('update', $article);
    
    if ($state = $request->input('state')) {
        $article->state()
            // Authorize transition
            ->authorize($state)
            // Transit to the new state, passing additional context
            ->transit($state, $request->all())
            // Now save model
            ->save();        
    }
}
```

The context will be validated while saving, and you may catch a `ValidationException`.

After all you may handle this context in [events](#events).

## Translations

You may define `State` and `Transition` objects with translatable caption.

```php
use \Soap\EloquentWorkflow\State;
use \Soap\EloquentWorkflow\Transition;
use \Soap\EloquentWorkflow\WorkflowBlueprint;

class ArticleWorkflow extends WorkflowBlueprint
{
    protected function states(): array
    {
        return [
            State::make('new')->as(__('Draft')),
            State::make('published')->as(__('Published'))
        ];
    }
    protected function transitions(): array
    {
        return [
            Transition::make('new', 'published')->as(__('Publish'))
        ];
    }
}
```

### Additional Attributes

Sometimes we need to add some additional attributes to the workflow states and transitions. For example, we may group states by levels and use this information to color states and transitions in user interface.

```php
use \Soap\EloquentWorkflow\State;
use \Soap\EloquentWorkflow\Transition;
use \Soap\EloquentWorkflow\WorkflowBlueprint;

class ArticleWorkflow extends WorkflowBlueprint
{
    protected function states(): array
    {
        return [
            State::make('new'),
            State::make('review')     ->set('level', 'warning'),
            State::make('published')  ->set('level', 'success'),
            State::make('correction') ->set('level', 'danger')
        ];
    }
    protected function transitions(): array
    {
        return [
            Transition::make('new', 'review')         ->set('level', 'warning'),
            Transition::make('review', 'published')   ->set('level', 'success'),
            Transition::make('review', 'correction')  ->set('level', 'danger'),
            Transition::make('correction', 'review')  ->set('level', 'warning')
        ];
    }
}
```

### Json Serialization

For user to interact with model's workflow we should pass the data to a frontend of the application:

```php
use Illuminate\Http\Request;

public function state(\App\Models\Article $article)
{    
    return $article->state()->toArray();
}
```

The payload will be like that:

```json
{
  "value": "review",
  "name": "Review",
  "transitions": [
    {
      "source": "review",
      "target": "publish",
      "name": "Publish",
      "issues": [
        "Publisher should provide a foreword."
      ],
      "level": "success"
    },
    {
      "source": "review",
      "target": "correction",
      "name": "Send to Correction",
      "rules": {
        "reason": ["required", "string", "min:100"]
      },
      "level": "danger"
    }
  ]
}
```

### Events

#### State Callback

You may define state callback(s), that will be called then state is reached.

Callback is a `callable` with `Model` and `context` arguments.

```php
use \Soap\EloquentWorkflow\State;

State::make('correcting')
    ->after(function(Article $article, ?State $previous, array $context) {
        $article->author->notify(
            new ArticleHasProblemNotification(
                $article, $context['reason']
            )
        );
    }); 
```

#### Transition Callback

You may define transition callback(s), that will be called after transition were successfully performed.

Callback is a `callable` with `Model` and `context` arguments.

```php
use \Soap\EloquentWorkflow\State;
use \Soap\EloquentWorkflow\Transition;

Transition::make('review', 'correcting')
    ->rules([
        'reason' => 'required|string|min:100'
    ])
    ->after(function(Article $article, ?State $previous, array $context) {
        $article->author->notify(
            new ArticleHasProblemNotification(
                $article, $context['reason']
            )
        );
    }); 
```

You may define few callbacks to a single transition.

#### EventListener

Transition generates `ModelTransited` event. You may define `EventListener` to listen to it.

```php
use \Soap\EloquentWorkflow\Events\ModelTransited;

class ModelTransitedListener
{
    public function handle(ModelTransited $event)
    {
        if ($event->model instanceof Article) {
            $article = $event->model;

            if ($event->transition->target()->is('correction')) {
                // Article was send to correction, the reason described in context
                $article->author->notify(
                    new ArticleHasProblemNotification(
                        $article, $event->transition->context('reason')
                    )
                );
            }
        }
    }
}
```

### Transition Logs

The Package may log transitions to database table. 

Register `\Soap\EloquentWorkflow\EloquentWorkflowServiceProvider` in `providers` section of `config/app.php`.

Run migrations:

    php artisan migrate

Edit `workflow.logging` into `config/eloquent-workflow.php`:

```php
    'workflow' => [
        'logging' => true
    ]
```

It's done.

To get logging records, add `\Soap\EloquentWorkflow\Traits\HasTransitionLog` to `Model` with workflow. It brings `transitions` relation.

Historical records presented by `\Soap\EloquentWorkflow\Models\TransitionLog` model, that holds information about transition performer, source and target states and a context, if it were provided.

### Blueprint Validation

The Package may validate Workflow Blueprint that you defined.

Register `\Soap\EloquentWorkflow\WorkflowServiceProvider` in `providers` section of `config/app.php`.

Run console command with blueprint classname:

    php artisan workflow:blueprint --class=App/Workflow/ArticleWorkflow

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Prasit Gebsaap](https://github.com/soap)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
