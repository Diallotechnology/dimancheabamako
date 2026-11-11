const xof = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' });

/** Montant en francs CFA. */
export const formatXof = (amount: number): string => xof.format(amount);
