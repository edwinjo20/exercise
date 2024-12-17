<?php
// Declaration of the namespace. This defines which "space" or "folder" this class belongs to.
// Here, the UserController class belongs to the "App\Controller" namespace.
namespace App\Controller;

use App\Entity\User; // Import the User class. This allows manipulation of User entities.
use App\Repository\UserRepository; // Import the UserRepository to access data from the 'user' table in the database.
use Doctrine\ORM\EntityManagerInterface; // Import EntityManagerInterface, which is used to interact with the database.
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; // Import the Symfony base controller class that provides utility methods for controllers.
use Symfony\Component\HttpFoundation\Request; // Import the Request class, which handles data sent in HTTP requests.
use Symfony\Component\HttpFoundation\Response; // Import the Response class, used to send an HTTP response.
use Symfony\Component\Routing\Annotation\Route; // Import the Route annotation to define routes for this controller.

#[Route('/users')] // This annotation defines the main route for all actions in the controller. All routes in this controller will start with '/users'.
class UserController extends AbstractController // Declaration of the UserController class, which extends AbstractController (the base class for Symfony controllers).
{
    #[Route('/', name: 'user_index', methods: ['GET'])] // This annotation defines the route to display the list of users. 'GET' indicates that this route responds to HTTP GET requests (requests to fetch data).
    public function index(UserRepository $userRepository): Response // The index() method retrieves all users from the database via UserRepository and displays them.
    {
        $users = $userRepository->findAll(); // Calls the findAll() method of UserRepository to retrieve all users from the database.

        return $this->render('user/index.html.twig', ['users' => $users]); // Renders the 'user/index.html.twig' view, passing the list of users to the view.
    }

    #[Route('/new', name: 'user_new', methods: ['GET', 'POST'])] // The '/new' route displays the creation form and handles form submission.
    public function new(Request $request, EntityManagerInterface $em): Response // The new() method handles the display and creation of new users.
    {
        if ($request->isMethod('POST')) { // If the request method is POST (indicating that the form was submitted).
            $user = new User(); // Create a new instance of the User entity.

            // Retrieve data submitted in the form and assign it to the $user entity.
            $user->setName($request->request->get('name')); // Sets the user's name from the request.
            $user->setEmail($request->request->get('email')); // Sets the email from the request.

            // Hash the password before saving it to the database.
            $hashedPassword = password_hash($request->request->get('password'), PASSWORD_BCRYPT); // Uses bcrypt to secure the password.
            $user->setPassword($hashedPassword); // Assigns the hashed password to the user.

            $role = $request->request->get('role', 'ROLE_USER'); // Retrieve the role from the form, defaulting to 'ROLE_USER'.
            $user->setRoles([$role]); // Assigns the role to the user.

            $em->persist($user); // Prepares the $user entity to be saved to the database.
            $em->flush(); // Saves the data to the database.

            return $this->redirectToRoute('user_index'); // Redirects to the user list page after adding the new user.
        }

        return $this->render('user/new.html.twig'); // If the method is GET (creation form), display the form.
    }
    
    #[Route('/{id}/edit', name: 'user_edit', methods: ['GET', 'POST'])] // The '/{id}/edit' route allows editing an existing user.
    public function edit(User $user, Request $request, EntityManagerInterface $em): Response // The edit() method allows modifying an existing user's information.
    {
        if ($request->isMethod('POST')) { // If the request method is POST (form submitted).
            // Retrieve and update the user's information.
            $user->setName($request->request->get('name')); // Updates the user's name.
            $user->setEmail($request->request->get('email')); // Updates the user's email.

            $role = $request->request->get('role', 'ROLE_USER'); // Retrieve and update the user's role.
            $user->setRoles([$role]); // Updates the user's role.

            $em->flush(); // Saves the changes made to the user in the database.

            return $this->redirectToRoute('user_index'); // Redirects to the user list page after editing.
        }

        return $this->render('user/edit.html.twig', ['user' => $user]); // Displays the form with the user's data for editing.
    }

    #[Route('/{id}/delete', name: 'user_delete', methods: ['POST'])] // The '/{id}/delete' route allows deleting a user.
    public function delete(User $user, EntityManagerInterface $em): Response // The delete() method allows removing an existing user.
    {
        $em->remove($user); // Removes the user from the database.
        $em->flush(); // Saves the deletion in the database.

        return $this->redirectToRoute('user_index'); // Redirects to the user list page after deletion.
    }
}
