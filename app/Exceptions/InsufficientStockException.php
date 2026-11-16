<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    /**
     * @param  array<int, array{idbarangjasa: string, nama_barang: string, diminta: int, tersedia: int}>  $shortages
     */
    public function __construct(
        public readonly string $idbarangjasa,
        public readonly string $namaBarang,
        public readonly int $diminta,
        public readonly int $tersedia,
        public readonly array $shortages = [],
    ) {
        parent::__construct($this->buildMessage());
    }

    /**
     * @param  array<int, array{idbarangjasa: string, nama_barang: string, diminta: int, tersedia: int}>  $shortages
     */
    public static function forShortages(array $shortages): self
    {
        $first = $shortages[0];

        return new self(
            $first['idbarangjasa'],
            $first['nama_barang'],
            $first['diminta'],
            $first['tersedia'],
            $shortages,
        );
    }

    /** @return array<int, string> */
    public function shortageMessages(): array
    {
        $shortages = $this->shortages !== [] ? $this->shortages : [[
            'idbarangjasa' => $this->idbarangjasa,
            'nama_barang' => $this->namaBarang,
            'diminta' => $this->diminta,
            'tersedia' => $this->tersedia,
        ]];

        return array_map(
            fn (array $shortage): string => sprintf(
                '%s (butuh %d, tersedia %d)',
                $shortage['nama_barang'],
                $shortage['diminta'],
                $shortage['tersedia'],
            ),
            $shortages,
        );
    }

    private function buildMessage(): string
    {
        return sprintf(
            'Stok tidak cukup untuk %s. Butuh %d, tersedia %d.',
            $this->namaBarang,
            $this->diminta,
            $this->tersedia,
        );
    }
}
