# Intégration BAN — Autocomplétion d'adresses

Cette note décrit l'intégration de l'API BAN (Base Adresse Nationale) pour l'autocomplétion et le remplissage des champs d'adresse.

## Vue d'ensemble

- **Client HTTP** : `src/Service/BanAddressClient.php`
- **Endpoint interne** : `GET /administration/adresses/ban`
- **Autocomplete front** : `assets/js/helpers/address-autocomplete.js`
- **Formulaires branchés (exemple)** : `templates/admin/venues/new.html.twig`, `templates/admin/venues/edit.html.twig`
- **Champs cachés BAN** : `src/Form/VenueType.php` (`addressSource`, `addressExternalId`, `addressLatitude`, `addressLongitude`)

## Backend

### Client BAN

Le service `App\Service\BanAddressClient` interroge l'API BAN :

```php
public function search(string $query, int $limit = 5): array
```

Il renvoie un tableau normalisé avec les propriétés utiles (label, line1, postcode, city, id, latitude, longitude).

### Endpoint interne

`App\Controller\Administration\AddressLookupController` expose :

```
GET /administration/adresses/ban?q=...&limit=...
```

Réponse :

```json
{
  "results": [
    {
      "label": "...",
      "line1": "...",
      "postcode": "...",
      "city": "...",
      "id": "...",
      "latitude": 49.7667,
      "longitude": 4.7167
    }
  ]
}
```

### Configuration

Dans `config/services.yaml`, le paramètre `ban_api_base` configure l'URL :

```yaml
parameters:
    ban_api_base: 'https://api-adresse.data.gouv.fr'
```

## Frontend

### Autocomplete JS

Le helper `initAddressAutocomplete` (fichier `assets/js/helpers/address-autocomplete.js`) :

- écoute les champs avec `data-address-autocomplete="true"`
- interroge l'endpoint interne
- propose une liste de suggestions
- remplit automatiquement les champs associés (code postal, ville, etc.)

### Attributs requis

Ajouter ces `data-attributes` sur le champ adresse principale (line1) :

```
data-address-autocomplete="true"
data-address-endpoint="..."
data-address-postal-id="..."
data-address-city-id="..."
data-address-country-id="..."
data-address-source-id="..."
data-address-external-id="..."
data-address-latitude-id="..."
data-address-longitude-id="..."
```

### Gestion du pays (France / étranger)

Le champ Pays pilote le mode de saisie :

- **France (FR)** : auto-complétion active
- **Autre** : auto-complétion désactivée, saisie libre

Le helper JS met automatiquement `source = MANUAL` (et vide `externalId/lat/long`) si le pays n'est pas `FR`.

Un texte d'aide peut être ajouté sous le champ adresse :

```html
<p
  data-address-hint
  data-address-hint-fr="Autocomplétion disponible pour les adresses en France."
  data-address-hint-foreign="Saisie manuelle pour les adresses hors France."
></p>
```

### Exemple (Twig)

```twig
{{ include('components/_form_field.html.twig', {
    field: form.addressLine1,
    input_attr: {
        'data-address-autocomplete': 'true',
        'data-address-endpoint': path('app_admin_addresses_ban'),
        'data-address-postal-id': form.addressPostalCode.vars.id,
        'data-address-city-id': form.addressCity.vars.id,
        'data-address-country-id': form.addressCountry.vars.id,
        'data-address-source-id': form.addressSource.vars.id,
        'data-address-external-id': form.addressExternalId.vars.id,
        'data-address-latitude-id': form.addressLatitude.vars.id,
        'data-address-longitude-id': form.addressLongitude.vars.id
    }
}) }}
```

### Champs cachés à prévoir

Pour stocker les métadonnées BAN, ajouter des champs cachés dans le formulaire :

```php
->add('addressSource', HiddenType::class, ['property_path' => 'address.source'])
->add('addressExternalId', HiddenType::class, ['property_path' => 'address.externalId'])
->add('addressLatitude', HiddenType::class, ['property_path' => 'address.latitude'])
->add('addressLongitude', HiddenType::class, ['property_path' => 'address.longitude'])
```

## Tests

Un test unitaire vérifie le mapping de la réponse BAN :

- `tests/Unit/Service/BanAddressClientTest.php`

## Notes

- L'autocomplétion ne force pas l'adresse : l'utilisateur peut saisir librement.
- La source d'adresse est renseignée à `BAN` lorsque l'utilisateur choisit une suggestion.
- Pour d'autres formulaires, il suffit de réutiliser les attributs et champs cachés.
