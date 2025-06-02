<?php

namespace App\Project;

use App\Entity\GameSession;
use App\Entity\CardStat;
use App\Entity\Player as PlayerEntity;
use Doctrine\ORM\EntityManagerInterface;

/**
 * BlackJack class.
 */
class BlackJack
{
    /**
     * The deck of cards used in the game.
     * @var BlackJackDeck
     */
    private BlackJackDeck $deck;
    /**
     * The player in the game.
     * @var BlackJackPlayer
     */
    private BlackJackPlayer $player;
    /**
     * The dealer.
     * @var BlackJackPlayer
     */
    private BlackJackPlayer $dealer;
    /**
     * The current status of the game.
     * @var string
     */
    private string $status;
    /**
     * The Doctrine EntityManager.
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * The index of the currently active player hand.
     * @var int
     */
    private int $activeHandIndex = 0;
    /**
     * Initializes a new Blackjack game for a player.
     * @param string $playerName The name of the player.
     * @param EntityManagerInterface $entityManager The Doctrine EntityManager.
     */
    public function __construct(string $playerName, EntityManagerInterface $entityManager)
    {
        $this->deck = new BlackJackDeck();
        $this->deck->shuffle();
        $this->entityManager = $entityManager;
        $playerEntity = $entityManager->getRepository(PlayerEntity::class)->findOneBy(['name' => $playerName]);
        if (!$playerEntity) {
            $playerEntity = new PlayerEntity();
            $playerEntity->setName($playerName);
            $playerEntity->setBalance(1000);
            $entityManager->persist($playerEntity);
            $entityManager->flush();
        }
        $this->player = new BlackJackPlayer($playerName, $playerEntity);
        $this->dealer = new BlackJackPlayer('Dealer', null);
        $this->status = 'not_started';
    }
    /**
     * Creates and returns a new deck of cards.
     * @return BlackJackDeck A fresh shuffled deck.
     */
    protected function createDeck(): BlackJackDeck
    {
        return new BlackJackDeck();
    }
    /**
     * Starts a new Blackjack game with the specified number of hands and bet per hand.
     * @param int $numHands The number of hands to play.
     * @param int $betPerHand The bet amount per hand.
     * @return bool True if the game started successfully, or false if invalid or insufficient funds.
     */
    public function startGame(int $numHands, int $betPerHand): bool
    {
        if ($numHands < 1 || $numHands > 3) {
            return false;
        }
        $totalBet = $betPerHand * $numHands;
        if ($this->player->getBalance() < $totalBet) {
            return false;
        }
        $gameSession = new GameSession();
        $gameSession->setPlayer($this->player->getEntity());
        $gameSession->setNumHands($numHands);
        $gameSession->setBetPerHand($betPerHand);
        $gameSession->setOutcome('ongoing');
        $this->entityManager->persist($gameSession);
        $this->entityManager->flush();
        $this->deck = $this->createDeck();
        $this->deck->shuffle();
        $this->player->clearHands();
        for ($i = 0; $i < $numHands; $i++) {
            $this->player->initializeNewHand($i);
            if (!$this->player->placeBet($betPerHand, $i)) {
                return false;
            }
            $card1 = $this->deck->draw();
            $card2 = $this->deck->draw();
            if ($card1 && $card2) {
                $this->player->addCard($card1, $i);
                $this->player->addCard($card2, $i);
                $this->updateCardStat($card1->getRank());
                $this->updateCardStat($card2->getRank());
            } else {
                return false;
            }
        }
        $this->activeHandIndex = 0;
        foreach ($this->player->getHands() as $index => $hand) {
            $score = $this->player->getScore($index);
            if ($score > 21) {
                $this->player->bust($index);
            } elseif ($this->player->isBlackjack($index)) {
                $this->player->setHandState($index, 'finished');
            }
        }
        $card1 = $this->deck->draw();
        $card2 = $this->deck->draw();
        if ($card1 && $card2) {
            $this->dealer->addCard($card1);
            $this->dealer->addCard($card2);
            $this->updateCardStat($card1->getRank());
            $this->updateCardStat($card2->getRank());
        } else {
            return false;
        }
        if ($this->dealer->isBlackjack()) {
            $this->playDealer();
            $this->evaluateGame();
            $this->status = 'game_over';
        } else {
            $this->activeHandIndex = $this->findNextActiveHand(-1);
            if ($this->activeHandIndex === -1) {
                $this->playDealer();
                $this->evaluateGame();
                $this->status = 'game_over';
            } else {
                $this->status = 'ongoing';
            }
        }
        return true;
    }
    /**
     * Handles the player's 'hit' action, drawing a card for the specified hand.
     * @param int $handIndex The index of the hand to hit.
     * @return bool True if the card was drawn and added successfully, false otherwise.
     */
    public function hit(int $handIndex): bool
    {
        if ($this->status !== 'ongoing' || $handIndex !== $this->activeHandIndex || !$this->player->isHandActive($handIndex)) {
            return false;
        }
        $card = $this->deck->draw();
        if (!$card) {
            return false;
        }
        $this->player->addCard($card, $handIndex);
        $this->updateCardStat($card->getRank());
        if ($this->player->getScore($handIndex) > 21) {
            $this->player->bust($handIndex);
        }
        $this->advanceTurnLogic();
        return true;
    }
    /**
     * Handles the player's 'stand' action, ending the turn for the specified hand.
     * @param int $handIndex The index of the hand to stand on.
     */
    public function stand(int $handIndex): void
    {
        if ($this->status !== 'ongoing' || $handIndex !== $this->activeHandIndex || !$this->player->isHandActive($handIndex)) {
            return;
        }
        $this->player->stand($handIndex);
        $this->advanceTurnLogic();
    }
    /**
     * Handles the player's 'double down' action, doubling the bet and drawing one card.
     * @param int $handIndex The index of the hand to double down on.
     * @return bool True if the action was successful, otherwise false.
     */
    public function doubleDown(int $handIndex): bool
    {
        if ($this->status !== 'ongoing' || $handIndex !== $this->activeHandIndex || !$this->player->isHandActive($handIndex)) {
            return false;
        }
        if (count($this->player->getHand($handIndex)) === 2) {
            $bet = $this->player->getBet($handIndex);
            if ($this->player->getBalance() >= $bet && $this->player->placeBet($bet, $handIndex)) {
                $card = $this->deck->draw();
                if ($card) {
                    $this->player->addCard($card, $handIndex);
                    $this->updateCardStat($card->getRank());
                    if ($this->player->getScore($handIndex) > 21) {
                        $this->player->bust($handIndex);
                    } else {
                        $this->player->setHandState($handIndex, 'finished');
                    }
                    $this->advanceTurnLogic();
                    return true;
                }
            }
        }
        return false;
    }
    /**
     * Handles the player's 'split' action, splitting a pair into two hands.
     * @param int $handIndex The index of the hand to split.
     * @return bool True if the split was successful, otherwise false.
     */
    public function split(int $handIndex): bool
    {
        if ($handIndex !== $this->activeHandIndex) {
            return false;
        }
        if (!$this->player->isHandActive($handIndex)) {
            return false;
        }
        if ($this->status !== 'ongoing') {
            return false;
        }
        $originalHandBet = $this->player->getBet($handIndex);
        if ($this->player->getBalance() < $originalHandBet) {
            return false;
        }
        $newHandIndex = count($this->player->getHands());
        if (!$this->player->splitHand($handIndex)) {
            return false;
        }
        $card1 = $this->deck->draw();
        $card2 = $this->deck->draw();
        if (!$card1 || !$card2) {
            return false;
        }
        $this->player->addCard($card1, $handIndex);
        $this->player->addCard($card2, $newHandIndex);
        $this->updateCardStat($card1->getRank());
        $this->updateCardStat($card2->getRank());
        $this->updateHandStateAfterAction($handIndex);
        $this->updateHandStateAfterAction($newHandIndex);
        $this->activeHandIndex = $handIndex;
        $this->advanceTurnLogic();
        return true;
    }
    /**
     * Updates the state of a hand after an action.
     * @param int $handIndex The index of the hand to update.
     */
    private function updateHandStateAfterAction(int $handIndex): void
    {
        $score = $this->player->getScore($handIndex);
        if ($score > 21) {
            $this->player->bust($handIndex);
        } elseif ($this->player->isBlackjack($handIndex) && count($this->player->getHand($handIndex)) == 2) {
            $this->player->setHandState($handIndex, 'finished');
        }
    }
    /**
     * Advances the game turn, moving to the next active hand or ending the game.
     */
    private function advanceTurnLogic(): void
    {
        $nextHand = $this->findNextActiveHand($this->activeHandIndex);
        if ($nextHand === -1) {
            $this->playDealer();
            $this->evaluateGame();
            $this->status = 'game_over';
        } else {
            $this->activeHandIndex = $nextHand;
            $this->status = 'ongoing';
        }
    }
    /**
     * Finds the next active hand for the player.
     * @param int $startIndex The index to start searching from.
     * @return int The index of the next active hand.
     */
    private function findNextActiveHand(int $startIndex): int
    {
        $numHands = count($this->player->getHands());
        for ($i = $startIndex + 1; $i < $numHands; $i++) {
            if ($this->player->isHandActive($i)) {
                return $i;
            }
        }
        for ($i = 0; $i <= $startIndex; $i++) {
            if ($this->player->isHandActive($i)) {
                return $i;
            }
        }
        return -1;
    }
    /**
     * Controls the dealer's play, drawing cards until the score is at least 17.
     */
    private function playDealer(): void
    {
        $anyPlayerHandNotBusted = false;
        foreach ($this->player->getHands() as $handIndex => $hand) {
            if ($this->player->getHandState($handIndex) !== 'busted') {
                $anyPlayerHandNotBusted = true;
                break;
            }
        }
        if (!$anyPlayerHandNotBusted && $this->dealer->getScore() < 21) {
            return;
        }
        while ($this->dealer->getScore() < 17) {
            $card = $this->deck->draw();
            if ($card) {
                $this->dealer->addCard($card);
                $this->updateCardStat($card->getRank());
            }
        }
    }
    /**
     * Evaluates the game outcome, comparing player and dealer scores.
     */
    private function evaluateGame(): void
    {
        $dealerScore = $this->dealer->getScore();
        $dealerBlackjack = $this->dealer->isBlackjack();
        $betsForDisplay = [];
        foreach ($this->player->getHands() as $handIndex => $hand) {
            $playerScore = $this->player->getScore($handIndex);
            $currentBet = $this->player->getBet($handIndex);
            $betsForDisplay[$handIndex] = $currentBet;
            $handState = $this->player->getHandState($handIndex);
            if ($handState === 'busted') {
                continue;
            } elseif ($this->player->isBlackjack($handIndex) && !$dealerBlackjack) {
                $this->player->winBet((int)($currentBet * 2.5), $handIndex);
            } elseif ($dealerScore > 21 || $playerScore > $dealerScore) {
                $this->player->winBet($currentBet * 2, $handIndex);
            } elseif ($playerScore === $dealerScore) {
                $this->player->winBet($currentBet, $handIndex);
            }
            $this->player->setHandState($handIndex, 'finished');
        }
        $this->status = 'game_over';
        $playerEntity = $this->player->getEntity();
        if ($playerEntity !== null) {
            $this->entityManager->persist($playerEntity);
        }
        $this->entityManager->flush();
        $gameSession = $this->entityManager->getRepository(GameSession::class)->findOneBy([], ['id' => 'DESC']);
        if ($gameSession) {
            $gameSession->setOutcome('completed');
            $this->entityManager->persist($gameSession);
            $this->entityManager->flush();
        }
        $this->player->setOriginalBets($betsForDisplay);
    }
    /**
     * Updates the card statistics for a drawn card.
     * @param string $cardValue The value of the drawn card.
     */
    private function updateCardStat(string $cardValue): void
    {
        $cardStat = $this->entityManager->getRepository(CardStat::class)->findOneBy(['cardValue' => $cardValue]);
        if (!$cardStat) {
            $cardStat = new CardStat();
            $cardStat->setCardValue($cardValue);
            $cardStat->setCount(0);
        }
        $cardStat->setCount($cardStat->getCount() + 1);
        $this->entityManager->persist($cardStat);
        $this->entityManager->flush();
    }
    /**
     * Get the index of the currently active player hand.
     * @return int The active hand index.
     */
    public function getActiveHandIndex(): int
    {
        return $this->activeHandIndex;
    }
    /**
     * Gets the player object.
     * @return BlackJackPlayer The player.
     */
    public function getPlayer(): BlackJackPlayer
    {
        return $this->player;
    }
    /**
     * Gets the dealer object.
     * @return BlackJackPlayer The dealer.
     */
    public function getDealer(): BlackJackPlayer
    {
        return $this->dealer;
    }
    /**
     * Gets the current game status.
     * @return string The game status.
     */
    public function getStatus(): string
    {
        return $this->status;
    }
    /**
     * Gets the game deck.
     * @return BlackJackDeck The deck of cards.
     */
    public function getDeck(): BlackJackDeck
    {
        return $this->deck;
    }
    /**
     * Gets the current game state as an array.
     * @return array<string, mixed> The game state data.
     */
    public function getState(): array
    {
        $playerHandsData = [];
        foreach ($this->player->getHands() as $handIndex => $hand) {
            $cards = array_map(function ($card) {
                return ['suit' => $card->getSuit(), 'value' => $card->getRank()];
            }, $hand);
            $playerHandsData[$handIndex] = [
                'data' => $cards,
                'bet' => $this->player->getBet($handIndex),
                'state' => $this->player->getHandState($handIndex)
            ];
        }
        $dealerHands = $this->dealer->getHands();
        $dealerCards = array_map(function ($card) {
            return ['suit' => $card->getSuit(), 'value' => $card->getRank()];
        }, $dealerHands[0] ?? []);
        $deckCards = array_map(function ($card) {
            return ['suit' => $card->getSuit(), 'value' => $card->getRank()];
        }, $this->deck->getCards());
        return [
            'player' => [
                'name' => $this->player->getName(),
                'hands' => $playerHandsData,
                'balance' => $this->player->getBalance(),
                'activeHandIndex' => $this->activeHandIndex
            ],
            'dealer' => ['hands' => [$dealerCards]],
            'deck' => $deckCards,
            'status' => $this->status,
        ];
    }
    /**
     * Restores the game state from a provided array.
     * @param array<string, mixed>|mixed[] $state The game data to restore.
     */
    public function reset(array $state): void
    {
        $this->deck = new BlackJackDeck();
        $deckCards = [];
        foreach ($state['deck'] ?? [] as $cardData) {
            $deckCards[] = new BlackJackGraphic($cardData['suit'], $cardData['value']);
        }
        $this->deck->setCards($deckCards);
        $playerName = $state['player']['name'] ?? 'Player';
        $playerEntity = $this->entityManager->getRepository(PlayerEntity::class)->findOneBy(['name' => $playerName]);
        $this->player = new BlackJackPlayer($playerName, $playerEntity);
        $playerHandsData = $state['player']['hands'] ?? [];
        $restoredHands = [];
        $restoredBets = [];
        $restoredHandStates = [];
        foreach ($playerHandsData as $handIndex => $handData) {
            $restoredCards = [];
            foreach ($handData['data'] ?? [] as $cardData) {
                $restoredCards[] = new BlackJackGraphic($cardData['suit'], $cardData['value']);
            }
            $restoredHands[$handIndex] = $restoredCards;
            $restoredBets[$handIndex] = (int)($handData['bet'] ?? 0);
            $restoredHandStates[$handIndex] = $handData['state'] ?? '';
        }
        $this->player->setHands($restoredHands);
        $this->player->setAllBets($restoredBets);
        $this->player->setAllHandStates($restoredHandStates);
        $this->player->setBalance((int)($state['player']['balance'] ?? 0));
        $this->dealer = new BlackJackPlayer('Dealer');
        foreach ($state['dealer']['hands'][0] ?? [] as $cardData) {
            $card = new BlackJackGraphic($cardData['suit'], $cardData['value']);
            $this->dealer->addCard($card);
        }
        $this->status = $state['status'] ?? 'not_started';
        $this->activeHandIndex = $state['player']['activeHandIndex'] ?? 0;
    }
}
