# Tic Tac Toe APIs
This is a simple implementation in Symfony 7 of the APIs needed to play a tic tac toe game.

## Installation
1. Clone the repository.
2. Go into the project folder and run `cd docker`.
3. Run `docker-compose up -d`.
4. Enter the php-fpm container by running `docker exec -it php-fpm bash`.
5. Run `composer install`.
6. Run `php bin/console doctrine:migrations:migrate`.
7. Create a pair of public/private keys for the JWT authentication by running `mkdir config/jwt && openssl genrsa -out config/jwt/private.pem && openssl rsa -in config/jwt/private.pem -pubout > config/jwt/public.pem`.
8. The application is now running on `http://localhost`.

## Usage

### Documentation
You can find the documentation of the APIs at the following link: `http://localhost/api/doc`.

### Authentication
First of all you need to retrieve a token to use the APIs. You can do this by sending a POST request to the `/api/login` endpoint with the following body:
```json
{
    "username": "admin@tictactoe.com",
    "password": "admin"
}
```
You will receive a token in the response. You need to use this token in the Authorization header (a Bearer one) of the following requests. <br/>

### Create a new game
To create a new game, you need to send a POST request to the `/api/game/start` endpoint.
You will receive a response with the game id and the board. <br/>
```json
{
    "result": "OK",
    "return": {
      "game_id": "game_id",
      "next_player": 1,
      "board": ["", "", "", "", "", "", "", "", ""],
      "winner": ""
    }
}
```
### Make a move
To make a move, you need to send a POST request to the `/api/game/move` endpoint with the following body:
```json
{
    "game_id": "game_id",
    "player": 1,
    "position": 0
}
```
You will receive a response with the updated status of the game
```json
{
    "result": "OK",
    "return": {
        "game_id": "game_id",
        "next_player": 2,
        "board": ["X", "", "", "", "", "", "", "", ""],
        "winner": ""
    }
}
```
### Run tests
Enter the php-fpm container by running the following command:
```bash
docker exec -it php-fpm bash
```

Run the tests:
```bash
php bin/phpunit
```

To see some fully played games, you can run the following command:
```bash
php bin/phpunit --filter testHorizontalWinGameByPlayer1
php bin/phpunit --filter testHorizontalWinGameByPlayer2
php bin/phpunit --filter testVerticalWinGame
php bin/phpunit --filter testDiagonalWinGame
php bin/phpunit --filter testNoWinner
```
FYI these tests outputs also the board of the game, so you can see the moves made by the players.

## Implementation details

### Docker environment
First of all, I created a docker environment to run the application. The environment is composed by a `PHP-FPM` container, a `nginx` container and a `MySQL` container. <br/> 
In the docker folder, you can find the `docker-compose.yml` file that defines the services and the volumes needed to run the application. <br/>
There are three more folders in the docker folder: mysql, nginx and php. Each of them contains the configuration files needed to run the services. php-fpm has also a Dockerfile to get the image, install the needed extensions, set up the working directory and installing composer. <br/>
Actually, the mysql folder exists but has no specific configurations (there is a my.cnf file but the content it is commented and does actually nothing. For specific needs, it can be edited and the content will be automatically taken into account.)

### Symfony
I created a new Symfony project in the app directory using the `composer create-project symfony/skeleton .` command. I installed some needed dependencies (`composer require symfony/maker-bundle --dev`, `composer require orm`) to create my entities.<br/>

### Tests
Before going on with the implementation, I created some tests to test the creation of the game and the moves. I used the `phpunit` library to run the tests. From Symfony docs, I used `composer require --dev symfony/test-pack` to install PHPUnit. <br/>
I created some basic unit tests, for example the ApiResponseTest and the GameTest. These tests do not need any db to work. <br/>
For end-to-end API tests (GameApiTest and ValidateGameApiTest), I wanted to check the interaction with the database, so I create a Base Test Class which drops and creates a test database. <br/>
To connect to the test database, I specified the credentials in the .env.test file, as well as the regular credentials for the application which are stored in the .env file. Of course this should be avoided for production credentials and credentials should be managed by some sort of secret management service. (Kubernetes + Cloud secret storage service, for example). <br/>
When creating the test database, there are some fixture pre-loaded in the db, for example the admin user, which is needed to test the authentication and some APIs. <br/>
Not coming from a Symfony/other framework background, I recognize that tests can be for sure improved (using mocks, for example, and taking advantage of the possibility to easily inject dependencies). I assume that I can mock the GameService to avoid the database dependency, I tried to play a bit trying to set the mock in the container but I didn't succeed. <br/>

### APIs

#### Authentication
Since I didn't want to leave my APIs open to the world, I installed the `lexik/jwt-authentication-bundle` to manage the authentication via a JWT token. I created a new User entity and I configured the bundle to use it. <br/>
For the sake of simplicity, since this was a small project to experiment with Symfony, I decided to protect the API endpoints with an ADMIN authentication, without authenticating the single players, but of course this can be done in a future extension of the project. The admin user is hardcoded in the database and it is created by the migration which creates the user table. <br/>
I added the relevant tests to test that endpoints are not called without authentication. <br/>

#### Game
API for the game are pretty basic, there is an endpoint to start a new game and an endpoint to make a move, which are described in the API doc and quickly in the Usage section. <br/>
The controller performs validation on the request. The service class handles objects manipulation and storage. I added functional validations in the object class, to check if the game exists, if the player is correct, if the position is correct and if the game is already finished. All validations have been tested. <br/>
I created translations for the validation messages to have more readability in the error (e.g. "game_id: This value should not be blank"). It is also possible to define different translations for different languages, thus having a UI which considers the user language. <br/>


#### OK - KO responses
I standardized the responses of the APIs, so that the client can understand if the request was successful or not and knows where the returned data are. For success and error purpose, it could also be enough to check the HTTP status code. <br/>
