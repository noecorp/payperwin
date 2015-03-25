<?php namespace App\Services\GameApi\League;

use App\Contracts\Service\GameApi\League\Match as MatchInterface;

class Match implements MatchInterface {

	/**
	 * The matches's id.
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * The champion played by this match's player.
	 *
	 * @var int
	 */
	protected $champion;

	/**
	 * The kills for this match's player.
	 *
	 * @var int
	 */
	protected $kills;

	/**
	 * The assists for this match's player.
	 *
	 * @var int
	 */
	protected $assists;

	/**
	 * The deaths for this match's player.
	 *
	 * @var int
	 */
	protected $deaths;

	/**
	 * Whether or not the match was won by the player.
	 *
	 * @var boolean
	 */
	protected $win;

	/**
	 * The match creation timestamp.
	 *
	 * @var int
	 */
	protected $timestamp;

	/**
	 * {@inheritdoc}
	 */
	public function createForPlayerId(array $data,$playerId)
	{
		$participantId = $this->findParticipantId($data['participantIdentities'],$playerId);

		if ($participantId === null) return null;

		$participant = $this->findParticipant($data['participants'],$participantId);

		if (!$participant === null) return null;

		$match = new static;

		$match->id = $data['matchId'];
		$match->timestamp = (int) round($data['matchCreation'] / 1000);

		$match->champion = $participant['championId'];
		$match->win = $participant['stats']['winner'];
		$match->kills = $participant['stats']['kills'];
		$match->assists = $participant['stats']['assists'];
		$match->deaths = $participant['stats']['deaths'];

		return $match;
	}

	/**
	 * {@inheritdoc}
	 */
	public function id()
	{
		return $this->id;
	}

	/**
	 * {@inheritdoc}
	 */
	public function timestamp()
	{
		return $this->timestamp;
	}

	/**
	 * {@inheritdoc}
	 */
	public function win()
	{
		return $this->win;
	}

	/**
	 * {@inheritdoc}
	 */
	public function champion()
	{
		return $this->champion;
	}

	/**
	 * {@inheritdoc}
	 */
	public function kills()
	{
		return $this->kills;
	}

	/**
	 * {@inheritdoc}
	 */
	public function assists()
	{
		return $this->assists;
	}

	/**
	 * {@inheritdoc}
	 */
	public function deaths()
	{
		return $this->deaths;
	}

	/**
	 * Get the participant identifier for a particular player.
	 *
	 * @param array $participantIdentities
	 * @param int $playerId
	 *
	 * @return int
	 */
	protected function findParticipantId(array $participantIdentities,$playerId)
	{
		foreach ($participantIdentities as $participantIdentity)
		{
			if ($participantIdentity['player']['summonerId'] == $playerId)
			{
				return $participantIdentity['participantId'];
			}
		}

		return null;
	}

	/**
	 * Get the stats for a particular participant.
	 *
	 * @param array $participants
	 * @param int $participantId
	 *
	 * @return array
	 */
	protected function findParticipant(array $participants,$participantId)
	{
		foreach ($participants as $participant)
		{
			if ($participant['participantId'] === $participantId)
			{
				return $participant;
			}
		}

		return null;
	}

}