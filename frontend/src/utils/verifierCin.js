import { createWorker } from 'tesseract.js';

// Mots-clés typiques retrouvés sur une carte d'identité nationale (CIN) malgache.
const MOTS_CLES_CIN = [
  'REPOBLIKA',
  "MADAGASIKARA",
  "CARTE D'IDENTITE",
  'CARTE DIDENTITE',
  'IDENTITE NATIONALE',
  'KARAPANONDRO',
  'KARA-PANONDRO',
  'FANONDROANA',
  'NATIONALITE'
];

function normaliser(texte) {
  return texte
    .toUpperCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '');
}

export async function verifierImageCin(fichier, onProgress) {
  const worker = await createWorker('fra', 1, {
    logger: (m) => {
      if (m.status === 'recognizing text' && onProgress) {
        onProgress(Math.round(m.progress * 100));
      }
    }
  });

  try {
    const { data } = await worker.recognize(fichier);
    const texteNormalise = normaliser(data.text || '');

    const contientMotCle = MOTS_CLES_CIN.some((mot) =>
      texteNormalise.includes(normaliser(mot))
    );
    const contientNumero = /\d{6,}/.test(texteNormalise);

    return {
      valide: contientMotCle || contientNumero,
      texteDetecte: data.text
    };
  } finally {
    await worker.terminate();
  }
}