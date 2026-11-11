/**
 * Champs communs aux formulaires Product/Create et Product/Update.
 * Les saisies texte restent des chaînes (le serveur valide les entiers), les cases à cocher sont
 * des booléens (convertis en 1/0 à l'envoi), les fichiers sont typés tels que le navigateur les fournit.
 */
export interface ProductFields {
    nom: string;
    reference: string;
    categorie_id: string;
    color: string;
    taille: string;
    poids: string;
    prix: string;
    stock: string;
    resume: string;
    description: string;
    favoris: boolean;
    is_preorder: boolean;
    cover: File | null;
    image: File[];
    video: File | null;
}
