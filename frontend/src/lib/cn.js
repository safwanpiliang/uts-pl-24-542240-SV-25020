import { clsx } from 'clsx'
import { extendTailwindMerge } from 'tailwind-merge'

// Token ukuran teks kustom (text-h1..h7, text-b1..b5) dibagi satu prefix
// `text-` dengan warna; tanpa config ini tailwind-merge menganggap keduanya
// satu grup dan membuang salah satunya.
const twMerge = extendTailwindMerge({
  extend: {
    classGroups: {
      'font-size': [{ text: ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'h7', 'b1', 'b2', 'b3', 'b4', 'b5'] }],
    },
  },
})

export function cn(...inputs) {
  return twMerge(clsx(inputs))
}
