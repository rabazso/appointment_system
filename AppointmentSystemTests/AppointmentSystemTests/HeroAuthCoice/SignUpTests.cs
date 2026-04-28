using OpenQA.Selenium;
using SeleniumExtras.WaitHelpers;

namespace AppointmentSystemTests.HeroAuthCoice
{
    public class SignUpTests:Base
    {
        [Test]
        public void SignUpBtnFromAuthChoiceSignIn_ShouldOpenSignUpModal()
        {
            OpenSignUpFromHero();

            var authModal = VisibleTestId("auth-modal");

            Assert.That(authModal.Displayed, Is.True);
        }

        [Test]
        public void SuccessfulSignUp_ShouldRedirectToBooking()
        {
            OpenSignUpFromHero();
            FillSignUpForm("Example", "Name", UniqueEmail(), "SafePassword123.", "SafePassword123.");

            ClickTestId("signup-submit");

            wait.Until(ExpectedConditions.UrlContains("/booking"));
        }

        [TestCase("SafePassword123")]
        [TestCase("safesassword123.")]
        [TestCase("SafePasswordsafe.")]
        [TestCase("1234567891011.")]
        [TestCase("..............")]
        public void SignUpShouldFailDueToPasswordRegulations(string password)
        {
            OpenSignUpFromHero();
            FillSignUpForm("Example", "Name", UniqueEmail(), password, password);

            ClickTestId("signup-submit");

            Assert.That(VisibleTestId("auth-modal").Displayed, Is.True);
            Assert.That(driver.Url.Contains("/booking"), Is.False);
        }

        [TestCase("SafePassword123.", "SafePassword123..")]
        public void SignUpShouldFailDueToConfPasswordNotMatchingPassword(string password, string confirmpass)
        {
            OpenSignUpFromHero();
            FillSignUpForm("Example", "Name", UniqueEmail(), password, confirmpass);

            ClickTestId("signup-submit");

            Assert.That(WaitForValidationMessage().Displayed, Is.True);
            Assert.That(driver.Url.Contains("/booking"), Is.False);
        }

        [TestCase("exampleemail")]
        [TestCase("exampleemail@")]
        [TestCase("@exampleemail")]
        [TestCase("@")]
        public void SignUpShouldFailDueToEmailErrors(string email)
        {
            OpenSignUpFromHero();
            FillSignUpForm("Example", "Name", email, "SafePassword123.", "SafePassword123.");

            ClickTestId("signup-submit");

            Assert.That(WaitForValidationMessage().Displayed, Is.True);
            Assert.That(driver.Url.Contains("/booking"), Is.False);
        }

        [TestCase("1122")]
        [TestCase("....")]
        public void SignUpShouldFailDueFirstNameRegulations(string firstname)
        {
            OpenSignUpFromHero();
            FillSignUpForm(firstname, "Name", UniqueEmail(), "SafePassword123.", "SafePassword123.");

            ClickTestId("signup-submit");

            Assert.That(WaitForValidationMessage().Displayed, Is.True);
            Assert.That(driver.Url.Contains("/booking"), Is.False);
        }

        [TestCase("1122")]
        [TestCase("....")]
        public void SignUpShouldFailDueLastNameRegulations(string lastname)
        {
            OpenSignUpFromHero();
            FillSignUpForm("Example", lastname, UniqueEmail(), "SafePassword123.", "SafePassword123.");

            ClickTestId("signup-submit");

            Assert.That(WaitForValidationMessage().Displayed, Is.True);
            Assert.That(driver.Url.Contains("/booking"), Is.False);
        }

        private void OpenSignUpFromHero()
        {
            ClickTestId("book-now-btn");
            ClickTestId("login-btn");
            ClickTestId("signupbtn");
            VisibleTestId("auth-modal");
        }

        private void FillSignUpForm(string firstName, string lastName, string email, string password, string confirmPassword)
        {
            TypeTestId("firstname-input", firstName);
            TypeTestId("lastname-input", lastName);
            TypeTestId("email-input", email);
            TypeTestId("password-input", password);
            TypeTestId("confpass-input", confirmPassword);
        }
    }
}
