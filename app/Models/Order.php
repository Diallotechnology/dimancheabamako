<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['payment', 'client_id', 'reference', 'adresse', 'postal', 'ville', 'country_id', 'transport_id', 'etat'];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['client', 'products', 'country'];

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

    protected function getCreatedAtAttribute(string $date): string
    {
        return Carbon::parse($date)->format('d/m/Y H:i:s');
    }

    public function generateId(string $prefix_type)
    {
        $currentYear = Carbon::today()->format('Y');
        $prefix = $prefix_type.$currentYear.'-';

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
                $sequence = (int) substr($lastCourrier->reference, strlen($prefix));
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
}
