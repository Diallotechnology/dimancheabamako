<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $nom
 * @property string $description
 * @property int $promo
 * @property string $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Product> $products
 * @property-read int|null $products_count
 * @method static \Database\Factories\CategoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category wherePromo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	final class Category extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $prenom
 * @property string $nom
 * @property string $contact
 * @property string $pays
 * @property string $email
 * @property string $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Order> $orders
 * @property-read int|null $orders_count
 * @method static \Database\Factories\ClientFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Client newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Client newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Client query()
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client wherePays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client wherePrenom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	final class Client extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $zone_id
 * @property string $nom
 * @property string $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Order> $orders
 * @property-read int|null $orders_count
 * @property-read Zone $zone
 * @method static \Database\Factories\CountryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Country newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Country newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Country query()
 * @method static \Illuminate\Database\Eloquent\Builder|Country whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Country whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Country whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Country whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Country whereZoneId($value)
 * @mixin \Eloquent
 */
	final class Country extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $type
 * @property int $taux
 * @property string $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\DeviseFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Devise newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Devise newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Devise query()
 * @method static \Illuminate\Database\Eloquent\Builder|Devise whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Devise whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Devise whereTaux($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Devise whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Devise whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	final class Devise extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $product_id
 * @property string $chemin
 * @property string $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Product $product
 * @method static \Database\Factories\ImageFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Image newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Image newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Image query()
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereChemin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	final class Image extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $client_id
 * @property int $country_id
 * @property int $transport_id
 * @property string|null $reference
 * @property string|null $trans_ref
 * @property string|null $trans_state
 * @property string $adresse
 * @property string|null $postal
 * @property string $ville
 * @property string $poids
 * @property string $shipping
 * @property string|null $commentaire
 * @property string $etat
 * @property string $created_at
 * @property Carbon|null $updated_at
 * @property-read Client $client
 * @property-read Country $country
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Product> $products
 * @property-read int|null $products_count
 * @property-read Transport $transport
 * @method static \Database\Factories\OrderFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereAdresse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCommentaire($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereEtat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePoids($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePostal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereShipping($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTransRef($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTransState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTransportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereVille($value)
 * @mixin \Eloquent
 * @property int|null $delai
 * @property int|null $metrage
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDelai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereMetrage($value)
 */
	final class Order extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $trans_ref
 * @property string|null $trans_state
 * @property string $name
 * @property string $contact
 * @property string $lien
 * @property int $montant
 * @property string $etat
 * @property string $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayLink newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayLink newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayLink query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayLink whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayLink whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayLink whereEtat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayLink whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayLink whereLien($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayLink whereMontant($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayLink whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayLink whereTransRef($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayLink whereTransState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayLink whereUpdatedAt($value)
 */
	final class PayLink extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $prenom
 * @property string $nom
 * @property string $pays
 * @property string $contact
 * @property string $email
 * @property string $password
 * @property string $role
 * @property string $token
 * @property \Illuminate\Support\Carbon $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration wherePays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration wherePrenom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration whereUpdatedAt($value)
 */
	final class PendingRegistration extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property float $min
 * @property float $max
 * @property string $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Shipping> $shippings
 * @property-read int|null $shippings_count
 * @method static \Database\Factories\PoidsFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Poids newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Poids newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Poids query()
 * @method static \Illuminate\Database\Eloquent\Builder|Poids whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Poids whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Poids whereMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Poids whereMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Poids whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	final class Poids extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $categorie_id
 * @property string $reference
 * @property string $nom
 * @property int $prix
 * @property int $favoris
 * @property float $poids
 * @property int $stock
 * @property string|null $color
 * @property string|null $taille
 * @property string $resume
 * @property string|null $description
 * @property string|null $video
 * @property string $cover
 * @property string $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Category $categorie
 * @property-read string $prix_final
 * @property-read string $prix_format
 * @property-read string $prix_promo
 * @property-read int $reduction
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Image> $images
 * @property-read int|null $images_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Order> $orders
 * @property-read int|null $orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Promotion> $promotions
 * @property-read int|null $promotions_count
 * @method static Builder|Product byStock()
 * @method static \Database\Factories\ProductFactory factory($count = null, $state = [])
 * @method static Builder|Product newModelQuery()
 * @method static Builder|Product newQuery()
 * @method static Builder|Product query()
 * @method static Builder|Product whereCategorieId($value)
 * @method static Builder|Product whereColor($value)
 * @method static Builder|Product whereCover($value)
 * @method static Builder|Product whereCreatedAt($value)
 * @method static Builder|Product whereDescription($value)
 * @method static Builder|Product whereFavoris($value)
 * @method static Builder|Product whereId($value)
 * @method static Builder|Product whereNom($value)
 * @method static Builder|Product wherePoids($value)
 * @method static Builder|Product wherePrix($value)
 * @method static Builder|Product whereReference($value)
 * @method static Builder|Product whereResume($value)
 * @method static Builder|Product whereStock($value)
 * @method static Builder|Product whereTaille($value)
 * @method static Builder|Product whereUpdatedAt($value)
 * @method static Builder|Product whereVideo($value)
 * @mixin \Eloquent
 * @property string|null $slug
 * @property bool $is_preorder
 * @property int $status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Promotion> $activePromotion
 * @property-read int|null $active_promotion_count
 * @property-read int $prix_final_base
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsPreorder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereStatus($value)
 */
	final class Product extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nom
 * @property string $etat
 * @property int $reduction
 * @property string $debut
 * @property string $fin
 * @property string $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Product> $products
 * @property-read int|null $products_count
 * @method static \Database\Factories\PromotionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion query()
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereDebut($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereEtat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereFin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereReduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion active()
 */
	final class Promotion extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $transport_id
 * @property int $zone_id
 * @property int $poids_id
 * @property string $temps
 * @property int $montant
 * @property string $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read float $montant_devise
 * @property-read Poids $poids
 * @property-read Transport $transport
 * @property-read Zone $zone
 * @method static \Database\Factories\ShippingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Shipping newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Shipping newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Shipping query()
 * @method static \Illuminate\Database\Eloquent\Builder|Shipping whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shipping whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shipping whereMontant($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shipping wherePoidsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shipping whereTemps($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shipping whereTransportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shipping whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shipping whereZoneId($value)
 * @mixin \Eloquent
 */
	final class Shipping extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $text_one
 * @property string $text_two
 * @property string $paragraph
 * @property string $image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\SlideFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Slide newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Slide newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Slide query()
 * @method static \Illuminate\Database\Eloquent\Builder|Slide whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slide whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slide whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slide whereParagraph($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slide whereTextOne($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slide whereTextTwo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slide whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	final class Slide extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nom
 * @property string $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Transport> $orders
 * @property-read int|null $orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Shipping> $shippings
 * @property-read int|null $shippings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Zone> $zones
 * @property-read int|null $zones_count
 * @method static \Database\Factories\TransportFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Transport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transport query()
 * @method static \Illuminate\Database\Eloquent\Builder|Transport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transport whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transport whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	final class Transport extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property mixed $password
 * @property int $change_password
 * @property RoleEnum $role
 * @property int $etat
 * @property string|null $remember_token
 * @property string $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereChangePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEtat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	final class User extends \Eloquent implements \Illuminate\Contracts\Auth\MustVerifyEmail {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nom
 * @property string $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Country> $countries
 * @property-read int|null $countries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Shipping> $shippings
 * @property-read int|null $shippings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Transport> $transports
 * @property-read int|null $transports_count
 * @method static \Database\Factories\ZoneFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Zone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Zone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Zone query()
 * @method static \Illuminate\Database\Eloquent\Builder|Zone whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zone whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zone whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	final class Zone extends \Eloquent {}
}

