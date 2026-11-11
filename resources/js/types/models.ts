/*
 * Modèles tels que sérialisés vers Inertia (attributs bruts + relations chargées).
 * Les relations sont optionnelles : elles n'existent que si le contrôleur les charge.
 * Source : props envoyées par AdminController et les contrôleurs CRUD (audit, section 5).
 */

/** Dates formatées par les accesseurs `getCreatedAtAttribute` ou ISO brut selon le modèle. */
type Timestamps = {
    created_at: string;
    updated_at: string | null;
};

export type ProductStatus = 'draft' | 'published';

export type OrderState =
    | 'Enregistré'
    | 'En attente'
    | 'En cours'
    | 'Terminé'
    | 'Stock épuisé';

export interface Category extends Timestamps {
    id: number;
    nom: string;
    description: string;
    promo: number;
}

export interface Image extends Timestamps {
    id: number;
    product_id: number;
    chemin: string;
}

export interface Product extends Timestamps {
    id: number;
    categorie_id: number;
    reference: string;
    nom: string;
    slug: string | null;
    prix: number;
    poids: number;
    stock: number;
    favoris: boolean;
    is_preorder: boolean;
    status: ProductStatus;
    color: string | null;
    taille: string | null;
    resume: string;
    description: string | null;
    video: string | null;
    cover: string;
    categorie?: Pick<Category, 'id' | 'nom'>;
    images?: Image[];
    /** Commandes contenant le produit (`ProductController::show` charge `orders`, sans leur pays). */
    orders?: Order[];
    /** Ligne de commande (pivot order_product), présente via Order.products. */
    pivot?: { quantity: number; montant: number };
}

export interface Client extends Timestamps {
    id: number;
    prenom: string;
    nom: string;
    contact: string;
    pays: string;
    email: string;
    orders?: Order[];
}

export interface Zone extends Timestamps {
    id: number;
    nom: string;
    countries?: Country[];
}

export interface Country extends Timestamps {
    id: number;
    zone_id: number;
    nom: string;
    zone?: Zone;
}

export interface Transport extends Timestamps {
    id: number;
    nom: string;
    zones?: Zone[];
}

export interface Poids extends Timestamps {
    id: number;
    min: number;
    max: number;
}

export interface Shipping extends Timestamps {
    id: number;
    transport_id: number;
    zone_id: number;
    poids_id: number;
    temps: string;
    montant: number;
    zone?: Zone;
    transport?: Transport;
    poids?: Poids;
}

export interface Order extends Timestamps {
    id: number;
    client_id: number;
    country_id: number;
    transport_id: number;
    reference: string | null;
    trans_ref: string | null;
    trans_state: string | null;
    adresse: string;
    postal: string | null;
    ville: string;
    poids: string;
    shipping: string;
    commentaire: string | null;
    etat: OrderState;
    /** `withSum('products as totaux', 'order_product.montant')`. */
    totaux?: number | string | null;
    client?: Pick<Client, 'id' | 'nom'> & Partial<Client>;
    country?: Pick<Country, 'id' | 'nom'>;
    transport?: Pick<Transport, 'id' | 'nom'>;
    products?: Product[];
}

export interface Promotion extends Timestamps {
    id: number;
    nom: string;
    etat: string;
    reduction: number;
    debut: string;
    fin: string;
    products?: Product[];
}

export interface PayLink extends Timestamps {
    id: number;
    name: string;
    contact: string;
    lien: string;
    montant: number;
    etat: string;
    trans_ref: string | null;
    trans_state: string | null;
}

export interface Devise extends Timestamps {
    id: number;
    type: 'EUR' | 'CFA';
    taux: number;
}

export interface Slide extends Timestamps {
    id: number;
    text_one: string;
    text_two: string;
    paragraph: string;
    image: string;
}

export interface User extends Timestamps {
    id: number;
    name: string;
    email: string;
    role: 'Administrateur' | 'Secretaire' | 'Client';
    etat: number;
    change_password: boolean;
    email_verified_at: string | null;
}
