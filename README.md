# Recipe API Plugin for WordPress

The Recipe API Plugin is a custom WordPress plugin designed to handle food recipe data through a RESTful API. This plugin allows you to create, read, update, and delete recipes, making it easy to manage and share your favorite culinary creations on your WordPress website.

## Installation

To install the Recipe API Plugin on your WordPress site, follow these steps:

1. Download the plugin ZIP file from [GitHub](https://github.com/jakaria-istauk/wp-recipe-api).

2. Log in to your WordPress admin panel.

3. Navigate to **Plugins > Add New**.

4. Click the **Upload Plugin** button at the top of the page.

5. Choose the ZIP file you downloaded in Step 1 and click **Install Now**.

6. Activate the plugin by clicking the **Activate** button.

## Usage

Once the Recipe API Plugin is installed and activated, you can use it to manage food recipes through the API. The plugin provides the following API endpoints:

**Manage Recipe:**

- **GET /wp-json/recipe-api/v1/recipes**: Get a list of all recipes.

- **GET /wp-json/recipe-api/v1/recipe/{slug}**: Get a specific recipe by slug/name.

- **POST /wp-json/recipe-api/v1/recipe**: Create a new recipe.

- **PUT /wp-json/recipe-api/v1/recipe/{slug}**: Update an existing recipe by slug/name.

- **DELETE /wp-json/recipe-api/v1/recipe/{id}**: Delete a recipe by slug/name.

**Manage User:**

- **POST /wp-json/recipe-api/v1/user/signup**: Create a new user.
- **POST /wp-json/recipe-api/v1/user/login**: validate a user with email & password.


### Authentication

The Recipe API Plugin requires authentication for certain actions. By default, it uses WordPress user authentication. Ensure that you have the necessary permissions to perform actions like creating or updating recipes.

### Example Requests

Here are some examples of how to interact with the Recipe API using `curl`:

**Get a list of all recipes:**

```shell
curl -X GET https://yourwebsite.com/wp-json/recipe-api/v1/recipes
```

**Create a new recipe:**

```shell
curl -X POST -H "Content-Type: application/json" -H "Authorization: logintoken" -d '{"title": "Delicious Pasta", "ingredients": "Pasta, Sauce, Cheese", "instructions": "Cook pasta, add sauce, sprinkle cheese, and enjoy!" "image":"image.url/base64 image", "image_src_type":"url/file"}' https://yourwebsite.com/wp-json/recipe-api/v1/recipe
```

**Update an existing recipe:**

```shell
curl -X PUT -H "Content-Type: application/json" -H "Authorization: logintoken" -d '{"id":"recipeID","title": "Amazing Pasta", "ingredients": "Pasta, Tomato Sauce, Parmesan Cheese", "instructions": "Boil pasta, mix with tomato sauce, and sprinkle Parmesan cheese.""image":"image.url/base64 image", "image_src_type":"url/file"}' https://yourwebsite.com/wp-json/recipe-api/v1/recipe/slug
```

**Delete a recipe:**

```shell
curl -X DELETE -H "Content-Type: application/json" -H "Authorization: logintoken" -d ' https://yourwebsite.com/wp-json/recipe-api/v1/recipe/123
```

**Create a new user:**

```shell
curl -X POST -H "Content-Type: application/json" -d '{"first_name": "Jakaria", "last_name": "Istauk", "email": "user@mail.domain" "password":"password"}' https://yourwebsite.com/wp-json/recipe-api/v1/user/signup
```
**Validate a user:**

```shell
curl -X POST -H "Content-Type: application/json" -d '{"email": "user@mail.domain" "password":"password"}' https://yourwebsite.com/wp-json/recipe-api/v1/user/login
```

## Support and Contributions

If you encounter any issues or have suggestions for improving the Recipe API Plugin, please open an issue on [GitHub](https://github.com/jakaria-istauk/wp-recipe-api/issues).

Contributions to this project are welcome! Feel free to fork the repository, make changes, and create a pull request.

## License

This plugin is licensed under the MIT License. You can find the full license text in the [LICENSE](LICENSE) file.

## Author

This Recipe API Plugin for WordPress was developed by [Jakaria Istauk](https://profiles.wordpress.org/jakariaistauk/).