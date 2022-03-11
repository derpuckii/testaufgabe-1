<?php

namespace Components\GameBoard;

use Illuminate\Support\Facades\Storage;
use Components\Enums\GameBoardSliceType;
use Components\Enums\GameMark;
use Exception;
use Throwable;

class GameBoard
{
    // Board size
    const TTT_SIZE = 3;

    // File name for serialization
    const TTT_GAME = 'tic-tac-toe';

    // Board data storage
    private array $board;

    private function __construct()
    {
        // Create an empty 3x3 board
        $this->board = array_fill(0, self::TTT_SIZE * self::TTT_SIZE, GameMark::None);
    }

    /**
     * Checks position validity and throws an exception in case of an invalid position
     * @param int $x X position
     * @param int $y Y position
     * @return void
     * @throws Exception
     */
    private function validatePosition(int $x, int $y): void
    {
        if ($x < 0 || $x >= self::TTT_SIZE) throw new Exception("Invalid X position.");
        if ($y < 0 || $y >= self::TTT_SIZE) throw new Exception("Invalid Y position.");
    }

    /**
     * Returns the content of a given space
     * @param int $x
     * @param int $y
     * @return GameMark
     * @throws Exception
     */
    public function getSpace(int $x, int $y): GameMark
    {
        $this->validatePosition($x,$y);
        return $this->board[ $y * self::TTT_SIZE + $x ];
    }

    /**
     * Sets the content of a given space
     * @param int $x
     * @param int $y
     * @param GameMark $mark
     * @return void
     * @throws Exception
     */
    public function setSpace(int $x, int $y, GameMark $mark)
    {
        $this->validatePosition($x,$y);
        $this->board[ $y * self::TTT_SIZE + $x ] = $mark;
    }

    /**
     * Returns an entire row
     * @throws Exception
     */
    public function getRow(int $row ): GameBoardSlice
    {
        return new GameBoardSlice( $this, GameBoardSliceType::Row, self::TTT_SIZE, $row );
    }

    /**
     * Returns an entire column
     * @throws Exception
     */
    public function getColumn(int $col ): GameBoardSlice
    {
        return new GameBoardSlice( $this, GameBoardSliceType::Column, self::TTT_SIZE, $col );
    }

    /**
     * Returns the main diagonal
     * @throws Exception
     */
    public function getMainDiagonal(): GameBoardSlice {
        return new GameBoardSlice( $this, GameBoardSliceType::MainDiagonal, self::TTT_SIZE );
    }

    /**
     * Returns the anti diagonal
     * @throws Exception
     */
    public function getAntiDiagonal(): GameBoardSlice
    {
        return new GameBoardSlice( $this, GameBoardSliceType::AntiDiagonal, self::TTT_SIZE );
    }

    /**
     * Loads a previously saved GameBoard or returns a new one of no previous save exists
     * @return GameBoard
     */
    static function load(): GameBoard
    {
        // Start with NULL
        $data = null;

        // If a game board file exists...
        if ( Storage::disk('local')->exists(self::TTT_GAME ) ) {

            // Attempt to load it
            try
            {
                // Deserialize data, only allow GameBord class
                $data = unserialize( Storage::disk('local')->get( self::TTT_GAME ), [
                    'allowed_classes' => self::class
                ] );

                // Make sure the resulting data is actually a GameBoard, otherwise throw Exception
                if ( !is_a( $data, self::class ) )
                    throw new Exception('Unable to unserialize.');

            }
            catch (Throwable $e)
            {
                // In case deserialization goes wrong, reset data to null
                $data = null;
            }
        }

        // Return deserialized data if it exists, otherwise create a new GameBoard
        return $data ?? new GameBoard();
    }

    /**
     * Saves the GameBoard
     * @return void
     */
    function save(): void
    {
        Storage::disk('local')->put(self::TTT_GAME, serialize( $this ) );
    }
}