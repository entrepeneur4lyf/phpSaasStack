use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ContactController extends BaseController
{
    private EmailServiceInterface $emailService;

    public function __construct(TwigRenderer $twigRenderer, EmailServiceInterface $emailService)
    {
        parent::__construct($twigRenderer);
        $this->emailService = $emailService;
    }

    public function sendContactForm(Request $request, Response $response): void
    {
        $formData = $request->post;

        // Validate form data here

        $this->emailService->sendTemplatedEmail(
            'admin@example.com',
            'New Contact Form Submission',
            'contact_form',
            $formData
        );

        $this->jsonResponse($response, ['success' => true, 'message' => 'Your message has been sent.']);
    }
}