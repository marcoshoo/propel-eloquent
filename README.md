# Propel Eloquent #

Propel Eloquent é um `behavior` para [Propel2](http://propelorm.org) que faz as classes de modelo extenderem virtualmente uma classe de modelo [Eloquent](http://laravel.com/docs/5.1/eloquent). Ao executar a construção do modelo, diversos métodos e atributos são criados na classe `Base` do modelo e também é gerada uma nova classe heradada de `Illuminate\Database\Eloquent\Model` no namespace `Eloquent` que é utilizada pela classe `Base` para simular um objeto `Eloquent`.

 O pacote também traz implementações para a injeção de dependência "hidratada" nos controladores tanto para objetos existentes(1) quanto vindos pelo request(2). Para que esses recursos funcionem é necessário que o `kernel` da aplicação extenda de `MarcosHoo\PropelEloquent\Http\Kernel`): 
 
```PHP
<?php
namespace App\Http\Controllers;

// Classe gerada pelo behavior que extende da classe de modelo Entity.
use App\Models\Request\EntityRequestObject;
use App\Http\Controllers\Controller;

/**
 * Resource Controller para o model Entity
 */
class EntityController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Entity $entity
     * @return \Illuminate\Http\Response
     */
    public function show(Entity $entity) { // Objeto existente "hidratado"(1)
        return response()->json($entity);
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Models\Request\EntityRequestObject $entity 
     * @return $this
     */
    public function store(EntityRequestObject $entity)
    {
        $entity->save(); // Objeto "hidratado" pelo request(2)
        
        return response()->json($entity);
    }
}
```

# Instalação #

Adicione os pacotes como dependência ao composer.json:

```javascript
{
    "require": {
    	"propel/propel-laravel" : "dev-develop",
        "marcoshoo/propel-eloquent": "dev-develop"
    }
}
```

Atualize suas dependências com `composer update` ou instale com `composer install`.

# Configuração #

Adicione o behavior `eloquent` no schema.xml:

```XML
    .
    .
    .
    <behavior name="eloquent">
	    <!-- Parâmetros opcionais para objeto Eloquent gerado -->
    	<parameter name="interfaces" value="Namespace1\Interface1, Namespace2\Interface2 as MyInterface, ..."/>
        <parameter name="traits" value="Namespace1\Trait1, Namespace2\Trait2 as MyTrait, ..."/>
        <parameter name="attributes" value="protected $myAttributes = [];\n"/>
        <parameter name="methods" value="public function getAuthIdentifier()\n    {\n        return $this->id;\n    } "/>
    </behavior>
  </table>
```

Reconstrua suas classes de modelo Propel:

```sh
$ php artisan propel:model:build
```
