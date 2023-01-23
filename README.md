# Pokémon TCG SDK

[![pokemontcg-developers on discord](https://img.shields.io/badge/discord-pokemontcg--developers-738bd7.svg)](https://discord.gg/dpsTCvg)
[![Build Status](https://travis-ci.org/PokemonTCG/pokemon-tcg-sdk-php.svg?branch=master)](https://travis-ci.org/PokemonTCG/pokemon-tcg-sdk-php)
[![Code Climate](https://codeclimate.com/github/PokemonTCG/pokemon-tcg-sdk-php/badges/gpa.svg)](https://codeclimate.com/github/PokemonTCG/pokemon-tcg-sdk-php)

This is the Pokémon TCG SDK PHP implementation. It is a wrapper around the Pokémon TCG API of [pokemontcg.io](http://pokemontcg.io/).

## Installation
    
    composer require jacobknox/pokemon-tcg-sdk-php
    
## Usage
    
### <p align="center">Set ApiKey and options</p>
[See the Guzzle 7 documentation for available options.](https://docs.guzzlephp.org/en/stable/request-options.html)
    
    Pokemon::Options(['verify' => true]);
    Pokemon::ApiKey('<YOUR_API_KEY_HERE>');

### <p align="center">Find a Card by id</p>

    $card = Pokemon::Card()->find('xy1-1');
    
### <p align="center">Filter Cards via query parameters</p>

    $cards = Pokemon::Card()->where(['set.name' => 'generations'])->where(['supertype' => 'pokemon'])->all();
    
    $cards = Pokemon::Card()->where([
        'set.name' => 'roaring skies',
        'subtypes' => 'ex'
    ])->all();
    
### <p align="center">Filter Cards via more complicated query parameters</p>

    $cards = Pokemon::Card()->where(['types' => ['OR', 'fire', 'water'])->where(['supertype' => 'pokemon'])->all();
    
    $cards = Pokemon::Card()->where([
        'types' => ['OR', 'fire', 'water'],
        'subtypes' => 'ex'
    ])->all();
    
### <p align="center">Order Cards</p>

There are four methods to order cards. You may use whichever one suits you. Please note that they will sorted first by the first array/list item then by the second and so on until the end of the array/list.

#### Specify attribute and whether to sort ascending or descending
    $cards = Pokemon::Card()->orderBy(['name' => 1, 'number' => -1])->all();

Permitted values to represent ascending: 1, 'ascending', ''.
Permitted values to represent descending: -1, 'descending', '-'.

#### Specify attributes with order indicator ('-' indicates descending, lack thereof indicates ascending)
    $cards = Pokemon::Card()->orderBy(['name', '-number'])->all();
    
#### Specify comma-separated list of attributes
    $cards = Pokemon::Card()->orderBy(['name,-number'])->all();
    
### <p align="center">Get all Cards</p>

    $cards = Pokemon::Card()->all();
    
### <p align="center">Paginate Card queries</p>

    $cards = Pokemon::Card()->where([
        'set.legalities.standard' => 'legal'
    ])->page(8)->pageSize(100)->all();
    
### <p align="center">Get Card pagination information</p>

    $pagination = Pokemon::Card()->where([
        'set.legalities.standard' => 'legal'
    ])->pagination();
    
### <p align="center">Find a Set by set code</p>

    $set = Pokemon::Set()->find('base1');
    
### <p align="center">Filter Sets via query parameters</p>

    $set = Pokemon::Set()->where(['legalities.standard' => 'legal'])->all();
    
### <p align="center">Paginate Set queries</p>

    $set = Pokemon::Set()->page(2)->pageSize(10)->all();
    
### <p align="center">Get Set pagination information</p>

    $pagination = Pokemon::Set()->pagination();
    
### <p align="center">Get all Sets</p>

    $sets = Pokemon::Set()->all();
    
### <p align="center">Get all Types</p>

    $types = Pokemon::Type()->all();
    
### <p align="center">Get all Subtypes</p>

    $subtypes = Pokemon::Subtype()->all();
    
### <p align="center">Get all Supertypes</p>

    $supertypes = Pokemon::Supertype()->all();
    
### <p align="center">Get all Rarities</p>

    $supertypes = Pokemon::Rarity()->all();
    
