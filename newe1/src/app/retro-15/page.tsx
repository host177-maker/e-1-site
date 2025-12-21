'use client';

import { useState, useEffect, useCallback } from 'react';

type Board = (number | null)[];

function generateSolvableBoard(): Board {
  // Start with solved state
  const solved: Board = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, null];

  // Shuffle by making random valid moves (ensures solvability)
  let board = [...solved];
  let emptyIndex = 15;

  for (let i = 0; i < 200; i++) {
    const neighbors = getNeighbors(emptyIndex);
    const randomNeighbor = neighbors[Math.floor(Math.random() * neighbors.length)];
    [board[emptyIndex], board[randomNeighbor]] = [board[randomNeighbor], board[emptyIndex]];
    emptyIndex = randomNeighbor;
  }

  return board;
}

function getNeighbors(index: number): number[] {
  const neighbors: number[] = [];
  const row = Math.floor(index / 4);
  const col = index % 4;

  if (row > 0) neighbors.push(index - 4); // up
  if (row < 3) neighbors.push(index + 4); // down
  if (col > 0) neighbors.push(index - 1); // left
  if (col < 3) neighbors.push(index + 1); // right

  return neighbors;
}

function isSolved(board: Board): boolean {
  for (let i = 0; i < 15; i++) {
    if (board[i] !== i + 1) return false;
  }
  return board[15] === null;
}

export default function Retro15Page() {
  const [board, setBoard] = useState<Board>(() => generateSolvableBoard());
  const [moves, setMoves] = useState(0);
  const [time, setTime] = useState(0);
  const [isRunning, setIsRunning] = useState(false);
  const [won, setWon] = useState(false);
  const [bestScore, setBestScore] = useState<{ moves: number; time: number } | null>(null);

  // Load best score from localStorage
  useEffect(() => {
    const saved = localStorage.getItem('retro15-best');
    if (saved) {
      setBestScore(JSON.parse(saved));
    }
  }, []);

  // Timer
  useEffect(() => {
    let interval: NodeJS.Timeout;
    if (isRunning && !won) {
      interval = setInterval(() => {
        setTime(t => t + 1);
      }, 1000);
    }
    return () => clearInterval(interval);
  }, [isRunning, won]);

  const handleTileClick = useCallback((index: number) => {
    if (won) return;

    const emptyIndex = board.indexOf(null);
    const neighbors = getNeighbors(emptyIndex);

    if (neighbors.includes(index)) {
      const newBoard = [...board];
      [newBoard[emptyIndex], newBoard[index]] = [newBoard[index], newBoard[emptyIndex]];
      setBoard(newBoard);
      setMoves(m => m + 1);

      if (!isRunning) setIsRunning(true);

      if (isSolved(newBoard)) {
        setWon(true);
        setIsRunning(false);

        // Check for new best score
        const currentScore = { moves: moves + 1, time };
        if (!bestScore || currentScore.moves < bestScore.moves ||
            (currentScore.moves === bestScore.moves && currentScore.time < bestScore.time)) {
          setBestScore(currentScore);
          localStorage.setItem('retro15-best', JSON.stringify(currentScore));
        }
      }
    }
  }, [board, won, isRunning, moves, time, bestScore]);

  // Keyboard controls
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (won) return;

      const emptyIndex = board.indexOf(null);
      const row = Math.floor(emptyIndex / 4);
      const col = emptyIndex % 4;

      let targetIndex: number | null = null;

      switch (e.key) {
        case 'ArrowUp':
          if (row < 3) targetIndex = emptyIndex + 4;
          break;
        case 'ArrowDown':
          if (row > 0) targetIndex = emptyIndex - 4;
          break;
        case 'ArrowLeft':
          if (col < 3) targetIndex = emptyIndex + 1;
          break;
        case 'ArrowRight':
          if (col > 0) targetIndex = emptyIndex - 1;
          break;
      }

      if (targetIndex !== null) {
        e.preventDefault();
        handleTileClick(targetIndex);
      }
    };

    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, [board, won, handleTileClick]);

  const resetGame = () => {
    setBoard(generateSolvableBoard());
    setMoves(0);
    setTime(0);
    setIsRunning(false);
    setWon(false);
  };

  const formatTime = (seconds: number) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
  };

  return (
    <div className="bg-[#1a1a2e] min-h-screen">
      {/* Hero section */}
      <div className="bg-gradient-to-b from-[#16213e] to-[#1a1a2e] py-8">
        <div className="container-custom text-center">
          <h1 className="text-3xl md:text-4xl font-bold text-white mb-2">
            <span className="text-[#e94560]">Ретро</span> 15
          </h1>
          <p className="text-gray-400">
            Классическая головоломка «Пятнашки»
          </p>
        </div>
      </div>

      {/* Game area */}
      <div className="container-custom py-8">
        <div className="max-w-md mx-auto">
          {/* Stats */}
          <div className="flex justify-between items-center mb-6 px-2">
            <div className="text-center">
              <div className="text-2xl font-bold text-[#e94560]">{moves}</div>
              <div className="text-xs text-gray-500 uppercase tracking-wide">Ходов</div>
            </div>
            <div className="text-center">
              <div className="text-2xl font-bold text-white font-mono">{formatTime(time)}</div>
              <div className="text-xs text-gray-500 uppercase tracking-wide">Время</div>
            </div>
            {bestScore && (
              <div className="text-center">
                <div className="text-lg font-bold text-[#62bb46]">
                  {bestScore.moves} / {formatTime(bestScore.time)}
                </div>
                <div className="text-xs text-gray-500 uppercase tracking-wide">Рекорд</div>
              </div>
            )}
          </div>

          {/* Game board */}
          <div className="relative bg-[#0f0f23] p-3 rounded-xl shadow-2xl">
            <div className="grid grid-cols-4 gap-2">
              {board.map((tile, index) => (
                <button
                  key={index}
                  onClick={() => handleTileClick(index)}
                  disabled={tile === null || won}
                  className={`
                    aspect-square rounded-lg text-2xl md:text-3xl font-bold
                    transition-all duration-150 ease-out
                    ${tile === null
                      ? 'bg-transparent'
                      : `bg-gradient-to-br from-[#e94560] to-[#c73e54] text-white
                         shadow-lg hover:scale-105 hover:shadow-xl
                         active:scale-95 cursor-pointer
                         ${getNeighbors(board.indexOf(null)).includes(index) ? 'ring-2 ring-[#62bb46] ring-opacity-50' : ''}`
                    }
                  `}
                  style={{
                    transform: tile === null ? 'scale(0)' : undefined,
                  }}
                >
                  {tile}
                </button>
              ))}
            </div>

            {/* Win overlay */}
            {won && (
              <div className="absolute inset-0 bg-black/80 rounded-xl flex flex-col items-center justify-center">
                <div className="text-4xl mb-2">🎉</div>
                <div className="text-2xl font-bold text-[#62bb46] mb-1">Победа!</div>
                <div className="text-gray-400 mb-4">
                  {moves} ходов за {formatTime(time)}
                </div>
                <button
                  onClick={resetGame}
                  className="px-6 py-2 bg-[#e94560] text-white font-bold rounded-lg hover:bg-[#c73e54] transition-colors"
                >
                  Играть снова
                </button>
              </div>
            )}
          </div>

          {/* Controls */}
          <div className="mt-6 flex justify-center gap-4">
            <button
              onClick={resetGame}
              className="px-6 py-3 bg-[#16213e] text-white font-bold rounded-lg hover:bg-[#1f2b47] transition-colors flex items-center gap-2"
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Новая игра
            </button>
          </div>

          {/* Instructions */}
          <div className="mt-8 bg-[#16213e] rounded-xl p-6">
            <h2 className="text-lg font-bold text-white mb-3">Как играть</h2>
            <ul className="space-y-2 text-gray-400 text-sm">
              <li className="flex items-start gap-2">
                <span className="text-[#e94560]">•</span>
                Нажимайте на плитки рядом с пустым местом, чтобы перемещать их
              </li>
              <li className="flex items-start gap-2">
                <span className="text-[#e94560]">•</span>
                Используйте стрелки на клавиатуре для управления
              </li>
              <li className="flex items-start gap-2">
                <span className="text-[#e94560]">•</span>
                Расположите числа от 1 до 15 по порядку
              </li>
              <li className="flex items-start gap-2">
                <span className="text-[#e94560]">•</span>
                Пустое место должно быть в правом нижнем углу
              </li>
            </ul>
          </div>

          {/* Fun fact */}
          <div className="mt-6 text-center text-gray-500 text-sm">
            <p>
              Игра «Пятнашки» была изобретена в 1874 году.<br />
              Существует более 10 триллионов возможных комбинаций!
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
