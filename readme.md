# LaravelMediaLibraryInput

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]
[![StyleCI][ico-styleci]][link-styleci]

This is where your description should go. Take a look at [contributing.md](contributing.md) to see a to do list.

## Installation

Via Composer

``` bash
$ composer require larabra/laravel-media-library-input
```

## Usage

Model

```php
// app\Model\News.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model as Model;
use Larabra\LaravelMediaLibraryInput\Casts\MediaCastAttribute; // <---
use Larabra\LaravelMediaLibraryInput\Models\MediableModel; // <---
use Spatie\MediaLibrary\HasMedia;

class News extends Model implements HasMedia
{
    use InteractsWithMedia;
    use MediableModel; // <--- create/add medias with form submit

    // "cover" is a fake field, so add it as append and create its cast

    protected $appends = [
        'cover',
    ];

    protected $casts = [
        'cover' => MediaCastAttribute::class,
    ];
    // ...
}
```

Controller

```php
// app\Http\Controllers\NewsController.php
<?php

namespace App\Http\Controllers;

use App\Repositories\NewsRepository;
use Larabra\LaravelMediaLibraryInput\Http\Controllers\MediableController;

class NewsController extends AppBaseController
{
    use MediableController; // <--- add controller methods to manager medias

    /** @var  NewsRepository */
    private $newsRepositorysitory;

    public function __construct(NewsRepository $newsRepository)
    {
        $this->newsRepository = $newsRepository;
    }
```

Views

```blade
{!! Form::label('cover', 'Capas:') !!}
{!! Form::medias('cover', ['multiple' => true]) !!}
```


## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email author email instead of using the issue tracker.

## Credits

- [Ennio Sousa][link-author]
- [All Contributors][link-contributors]

## License

MIT. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/larabra/laravel-media-library-input.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/larabra/laravel-media-library-input.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/larabra/laravel-media-library-input/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/larabra/laravel-media-library-input
[link-downloads]: https://packagist.org/packages/larabra/laravel-media-library-input
[link-travis]: https://travis-ci.org/larabra/laravel-media-library-input
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/enniosousa
[link-contributors]: ../../contributors
