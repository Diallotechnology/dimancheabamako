const xof = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' });

/** Montant en francs CFA (même rendu que `Price_format` de helper.js, sans embarquer les toasts hérités). */
export const formatXof = (amount: number): string => xof.format(amount);
