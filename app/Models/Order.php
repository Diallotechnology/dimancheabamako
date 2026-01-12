<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
 * @property int|null $delai
 * @property int|null $metrage
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDelai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereMetrage($value)
 * @mixin \Eloquent
 */
final class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['client_id', 'trans_ref', 'trans_state', 'reference', 'adresse', 'postal', 'ville', 'country_id', 'transport_id', 'etat', 'poids', 'shipping', 'commentaire'];

    /**
     * The products that belong to the Order
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot('quantity', 'montant');
    }

    /**
     * Get the pays that owns the Order
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the transport that owns the Order
     */
    public function transport(): BelongsTo
    {
        return $this->belongsTo(Transport::class);
    }

    /**
     * Get the client that owns the Order
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function getShipping(): int|string
    {
        if (session('devise') === 'EUR') {
            // Récupération du taux de conversion et du symbole de devise en fonction de la locale de la session
            $tauxConversion = session('devise') === 'EUR' ? Devise::whereType('EUR')->value('taux') : '';

            return number_format($this->shipping / $tauxConversion, 2);
        }
        if (session('devise') === 'CFA') {
            $this->shipping;
        }
    }

    public function getTaux(): int
    {
        // Récupération du taux de conversion et du symbole de devise en fonction de la locale de la session
        $tauxConversion = session('devise') === 'EUR' ? Devise::whereType('EUR')->value('taux') : '';

        return $tauxConversion;
    }

    public function generateId()
    {
        $currentYear = Carbon::today()->format('Y');
        $prefix = 'DAB'.$currentYear.'-';

        return DB::transaction(function () use ($prefix) {
            // Verrouille le dernier identifiant de courrier enregistré dans la base de données pour la mise à jour
            $lastCourrier = self::where('reference', 'like', $prefix.'%')->whereNotNull('reference')
                ->latest('id')
                ->lockForUpdate()
                ->first(['reference']);
            // Si aucun identifiant de courrier n'a été enregistré, définit le numéro de séquence à 0
            $sequence = 0;
            if ($lastCourrier) {
                // Récupère le numéro de séquence de l'identifiant de courrier précédent
                $sequence = (int) mb_substr($lastCourrier->reference, mb_strlen($prefix));
            }
            // Incrémente le numéro de séquence et génère le nouvel identifiant de courrier
            $sequence++;
            $newCourrierNumber = $prefix.$sequence;
            // Met à jour le numéro de courrier de l'instance courante
            $this->reference = $newCourrierNumber;
            $this->save();

            return $this;
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'country_id' => 'integer',
            'transport_id' => 'integer',
            'client_id' => 'integer',
        ];
    }

    protected function getCreatedAtAttribute(string $date): string
    {
        return Carbon::parse($date)->format('d/m/Y H:i:s');
    }
}
