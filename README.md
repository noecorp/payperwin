# PayPerWin

<a href="https://assembly.com/payperwin/bounties?utm_campaign=assemblage&utm_source=payperwin&utm_medium=repo_badge"><img src="https://asm-badger.herokuapp.com/payperwin/badges/tasks.svg" height="24px" alt="Open Tasks" /></a>

## Game result monetisation for eSports streamers.

- Initially launching for League of Legends streamers, using Riot's Public Game API.
- eSports Streamers get paid for in-game results such as wins, kills, assists, and KDA (Kill-Death-Assist ratio). This supplements their salary, streaming, donations, and other income by allowing their fans to reward entertaining in-game action. Sort of loosely conditional donations, or micro-sponsorships if you will.
- Streamers register on the platform and link to their PayPerWin profile page on their Twitch profile page.
- Fans/viewers register on the platform, top up their account, and pledge values to their favourite streamer's in-game results.
- Pledges can be customised with limits on number of games (next 5 wins) and number of kills/assists/KDA (reward max 8 kills per game).
- Every registered streamer's winning ranked game is tracked via Riot's API and pledges are automatically distributed by the system.
- Streamers get paid monthly based on the previous month's accrued pledges, minus the platform's commission.
- Game types limited to Ranked (Normal games are off-limits to API atm) WINS. Restricting to WINS prevents streamers from ruining teammate's games by focusing on monetised kills over winning.
- There should be logical built-in limits on weekly/monthly total pledge amounts per user and maximum rewardable kills/assists/KDA per game.

## Helping with development

Before you jump in and start hacking away at Pull Requests, first read our [Dev Contributing Guide](https://github.com/asm-products/payperwin/blob/master/CONTRIBUTING.md)!

## Assembly

This is a product being built by the Assembly community. You can help push this idea forward by visiting [https://assembly.com/payperwin](https://assembly.com/payperwin).

### How Assembly Works

Assembly products are like open-source and made with contributions from the community. Assembly handles the boring stuff like hosting, support, financing, legal, etc. Once the product launches we collect the revenue and split the profits amongst the contributors.

Visit [https://assembly.com](https://assembly.com) to learn more.