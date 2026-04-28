using OpenQA.Selenium;
using SeleniumExtras.WaitHelpers;

namespace AppointmentSystemTests.HeroAuthCoice
{
    public class SignInTests:Base
    {
        [Test]
        public void SignInFromAuthChoice_ShouldOpenLoginModal()
        {
            ClickTestId("book-now-btn");

            ClickTestId("login-btn");

            var authModal = VisibleTestId("auth-modal");

            Assert.That(authModal.Displayed, Is.True);
        }

        [Test]
        public void SuccessfulLogin_ShouldRedirectToBooking()
        {
            ClickTestId("book-now-btn");

            ClickTestId("login-btn");

            LoginAsCustomer();

            wait.Until(ExpectedConditions.UrlContains("/booking"));
        }

        [Test]
        public void SignInThroughHeaderShouldShowToast()
        {
            OpenLoginFromHeader();
            LoginAsCustomer();

            var toast = VisibleTestId("toast");

            Assert.That(toast.Text, Does.Contain("Successfully signed in"));
        }

        [Test]
        public void WhenSignedInBookBtnShouldNavigateToBookingPage()
        {
            OpenLoginFromHeader();
            LoginAsCustomer();

            ClickTestId("book-now-btn");

            wait.Until(ExpectedConditions.UrlContains("/booking"));
        }

        [TestCase("email")]
        [TestCase("email@")]
        [TestCase("@email")]
        [TestCase("@")]
        public void SignInWithNotValidEmailFormatShouldThrowError(string email)
        {
            OpenLoginFromHeader();

            TypeTestId("email-input", email);
            TypeTestId("password-input", "password");
            ClickTestId("signin-submit");

            Assert.That(WaitForValidationMessage().Displayed, Is.True);
        }

        [Test]
        public void SignInWithWrongCredentialsWillThrowError()
        {
            OpenLoginFromHeader();

            TypeTestId("email-input", "notregisteredemail@gmail.com");
            TypeTestId("password-input", "notregisteredpass");
            ClickTestId("signin-submit");

            var errorMessage = VisibleTestId("errormsg-login");
            string errorText = errorMessage.Text;

            Assert.That(errorText.Contains("Invalid credentials"), Is.True);
        }

        [Test]
        public void SignInWithEmptyFieldsWillThrowError()
        {
            OpenLoginFromHeader();

            ClickTestId("signin-submit");

            Assert.That(WaitForValidationMessage().Displayed, Is.True);
        }

        [Test]
        public void SignInWithEmptyPasswordFieldWillThrowError()
        {
            OpenLoginFromHeader();

            TypeTestId("email-input", "email@email.com");
            ClickTestId("signin-submit");

            Assert.That(WaitForValidationMessage().Displayed, Is.True);
        }
    }
}
