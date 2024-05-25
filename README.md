# Tic Tac Toe APIs
This is a simple implementation in Symfony 7 of the APIs needed to play a tic tac toe game.

## Installation
1. Clone the repository.
2. Go into the main folder (cd tic-tac-toe)
3. Run `docker-compose up -d`.
4. The application is now running on `http://localhost`.
5. Apply the migrations by running `docker-compose exec php bin/console doctrine:migrations:migrate`.

## Usage

### Authentication
First of all you need to retrieve a token to use the APIs. You can do this by sending a POST request to the `/api/login` endpoint with the following body:
```json
{
    "username": "admin@tictactoe.com",
    "password": "admin"
}
```
You will receive a token in the response. You need to use this token in the Authorization header of the following requests. <br/>

### Create a new game
To create a new game, you need to send a POST request to the `/api/game/start` endpoint.
You will receive a response with the game id and the board. <br/>

```json
{
  "id": "ttt6651f904140c4"
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

## Implementation details

### Docker environment
First of all, I created a docker environment to run the application. The environment is composed by a `PHP-FPM` container, a `nginx` container and a `MySQL` container. <br/> 
In the docker folder, you can find the `docker-compose.yml` file that defines the services and the volumes needed to run the application. <br/>
There are three more folders in the docker folder: mysql, nginx and php. Each of them contains the configuration files needed to run the services. php-fpm has also a Dockerfile to get the image, install the needed extensions, set up the working directory and installing composer. <br/>

### Symfony
I created a new Symfony project in the app directory using the `composer create-project symfony/skeleton .` command. I installed some needed dependencies (`composer require symfony/maker-bundle --dev`, `composer require orm`) to create my entities.<br/>

### Unit tests
Before going on with the implementation, I created some unit tests to test the creation of the game and the moves. I used the `phpunit` library to run the tests. From Symfony docs, I used `composer require --dev symfony/test-pack` to install PHPUnit. <br/>
Since I used a database as storage for the games, in every test where the db is needed, I create a test database and I drop it at the end of the test. <br/>
There are some fixture loaded, for example the admin user, which is needed to test the authentication and some APIs. <br/>


### APIs

#### Authentication
Since I didn't want to leave my API open to the world, I installed the `lexik/jwt-authentication-bundle` to manage the authentication via a JWT token. I created a new User entity and I configured the bundle to use it. <br/>
For the sake of simplicity, since this was a small project to experiment with Symfony, I decided to protect the API endpoints with an ADMIN authentication, without authenticating the single players, but of course this can be done in a future extension of the project. The admin user is hardcoded in the database and it is created by the migration which creates the user table. <br/>
I added the relevant tests into the api folder. <br/>

#### Game

