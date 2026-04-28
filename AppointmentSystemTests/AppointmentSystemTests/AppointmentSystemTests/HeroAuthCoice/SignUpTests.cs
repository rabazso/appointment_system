using OpenQA.Selenium;
using SeleniumExtras.WaitHelpers;
using System;
using System.Collections.Generic;
using System.Text;

namespace AppointmentSystemTests.HeroAuthCoice
{
    public class SignUpTests:Base
    {
        [Test]
        public void SignUpBtnFromAuthChoiceSignIn_ShouldOpenSignUpModal()
        {
            driver.FindElement(By.CssSelector("[data-testid='book-now-btn']")).Click();

            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='login-btn']"))).Click();
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='signupbtn']"))).Click();

            var authModal = wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='auth-modal']")));

            Assert.That(authModal.Displayed, Is.True);
        }

        [Test]
        public void SuccessfulSignUp_ShouldRedirectToBooking()
        {
            driver.FindElement(By.CssSelector("[data-testid='book-now-btn']")).Click();

            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='login-btn']"))).Click();
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='signupbtn']"))).Click();

            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='firstname-input']"))).SendKeys("Example");
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='lastname-input']"))).SendKeys("Name");
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='email-input']"))).SendKeys("name@example.com");
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='password-input']"))).SendKeys("SafePassword123.");
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='confpass-input']"))).SendKeys("SafePassword123.");

            driver.FindElement(By.CssSelector("[data-testid='signup-submit']")).Click();

            wait.Until(ExpectedConditions.UrlContains("/booking"));
        }

        [TestCase("SafePassword123")]
        [TestCase("safesassword123.")]
        [TestCase("SafePasswordsafe.")]
        [TestCase("1234567891011.")]
        [TestCase("..............")]
        [Test]
        public void SignUpShouldFailDueToPasswordRegulations(string password)
        {
            driver.FindElement(By.CssSelector("[data-testid='book-now-btn']")).Click();

            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='login-btn']"))).Click();
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='signupbtn']"))).Click();

            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='firstname-input']"))).SendKeys("Example");
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='lastname-input']"))).SendKeys("Name");
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='email-input']"))).SendKeys("name2@example.com");
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='password-input']"))).SendKeys(password);
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='confpass-input']"))).SendKeys(password);

            driver.FindElement(By.CssSelector("[data-testid='signup-submit']")).Click();

            var errorMessage = wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='errormsg-signup']")));
            string errorText = errorMessage.Text;

            Assert.That(errorText.Contains("Password must contain"), Is.True);
            Assert.That(driver.Url.Contains("/booking"), Is.False);
        }

        [TestCase("SafePassword123.", "SafePassword123..")]
        [Test]
        public void SignUpShouldFailDueToConfPasswordNotMatchingPassword(string password, string confirmpass)
        {
            driver.FindElement(By.CssSelector("[data-testid='book-now-btn']")).Click();

            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='login-btn']"))).Click();
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='signupbtn']"))).Click();

            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='firstname-input']"))).SendKeys("Example");
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='lastname-input']"))).SendKeys("Name");
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='email-input']"))).SendKeys("name2@example.com");
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='password-input']"))).SendKeys(password);
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='confpass-input']"))).SendKeys(confirmpass);

            driver.FindElement(By.CssSelector("[data-testid='signup-submit']")).Click();

            var errorMessage = wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='errormsg-signup']")));
            string errorText = errorMessage.Text;

            Assert.That(errorText.Contains("The given passwords must match"), Is.True);
            Assert.That(driver.Url.Contains("/booking"), Is.False);
        }

        [TestCase("exampleemail")]
        [TestCase("exampleemail@")]
        [TestCase("@exampleemail")]
        [TestCase("@")]
        [Test]
        public void SignUpShouldFailDueToEmailErrors(string email)
        {
            driver.FindElement(By.CssSelector("[data-testid='book-now-btn']")).Click();

            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='login-btn']"))).Click();
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='signupbtn']"))).Click();

            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='firstname-input']"))).SendKeys("Example");
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='lastname-input']"))).SendKeys("Name");
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='email-input']"))).SendKeys(email);
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='password-input']"))).SendKeys("SafePassword123.");
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='confpass-input']"))).SendKeys("SafePassword123.");

            driver.FindElement(By.CssSelector("[data-testid='signup-submit']")).Click();

            var errorMessage = wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='errormsg-signup']")));
            string errorText = errorMessage.Text;

            Assert.That(errorText.Contains("The email field must be a valid email address."), Is.True);
            Assert.That(driver.Url.Contains("/booking"), Is.False);
        }

        [TestCase("1122")]
        [TestCase("....")]
        [Test]
        public void SignUpShouldFailDueFirstNameRegulations(string firstname)
        {
            driver.FindElement(By.CssSelector("[data-testid='book-now-btn']")).Click();

            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='login-btn']"))).Click();
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='signupbtn']"))).Click();

            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='firstname-input']"))).SendKeys(firstname);
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='lastname-input']"))).SendKeys("Name");
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='email-input']"))).SendKeys("name2@example.com");
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='password-input']"))).SendKeys("SafePassword123.");
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='confpass-input']"))).SendKeys("SafePassword123.");

            driver.FindElement(By.CssSelector("[data-testid='signup-submit']")).Click();

            var errorMessage = wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='errormsg-signup']")));
            string errorText = errorMessage.Text;

            Assert.That(errorText.Contains("Name must not contain"), Is.True);
            Assert.That(driver.Url.Contains("/booking"), Is.False);
        }

        [TestCase("1122")]
        [TestCase("....")]
        [Test]
        public void SignUpShouldFailDueLastNameRegulations(string lastname)
        {
            driver.FindElement(By.CssSelector("[data-testid='book-now-btn']")).Click();

            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='login-btn']"))).Click();
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='signupbtn']"))).Click();

            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='firstname-input']"))).SendKeys("Example");
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='lastname-input']"))).SendKeys(lastname);
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='email-input']"))).SendKeys("name2@example.com");
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='password-input']"))).SendKeys("SafePassword123.");
            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='confpass-input']"))).SendKeys("SafePassword123.");

            driver.FindElement(By.CssSelector("[data-testid='signup-submit']")).Click();

            var errorMessage = wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='errormsg-signup']")));
            string errorText = errorMessage.Text;

            Assert.That(errorText.Contains("Name must not contain"), Is.True);
            Assert.That(driver.Url.Contains("/booking"), Is.False);
        }
    }
}
