<?php
namespace App\Controller\Visitor\Registration;



use App\Entity\User;
use App\Security\EmailVerifier;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/register', name: 'visitor.registration.register', methods:['GET','POST'])]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            // Encodons le mot de passe
            $passwordHashed = $userPasswordHasher->hashPassword($user, $form->get('password')->getData());

            // Initialisation la propriété password de l'utilisateur qui s'inscrit avec le mot de passe encodé
            $user->setPassword($passwordHashed);


            $entityManager->persist($user);

            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('visitor.registration.verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('medecine-du-monde@gmail.com', 'Jean Dupont'))
                    ->to($user->getEmail())
                    ->subject('Confirmation de votre compte sur le blog de Jean Dupont')
                    ->htmlTemplate('emails/confirmation_email.html.twig')
            );

            return $this->redirectToRoute('visitor.registration.waiting_for_email_verification');
        }

        return $this->render('pages/visitor/registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }


    #[Route('/register/waiting-for-email-verification', name: 'visitor.registration.waiting_for_email_verification', methods:['GET'])]
    public function waitingForEmailVerif(): Response
    {
        return $this->render("pages/visitor/registration/waiting_for_email_verification.html.twig");
    }

    


    #[Route('/verify/email', name: 'visitor.registration.verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $id = $request->query->get('id');

        if (null === $id) 
        {
            return $this->redirectToRoute('visitor.registration.register');
        }

        $user = $userRepository->find($id);

        if (null === $user) 
        {
            return $this->redirectToRoute('visitor.registration.register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try 
        {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } 
        catch (VerifyEmailExceptionInterface $exception) 
        {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('visitor.registration.register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Votre compte a bien été vérifié. Vous pouvez vous connecter.');

        return $this->redirectToRoute('visitor.welcome.index');
    }
}
