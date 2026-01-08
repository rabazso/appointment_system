using OpenQA.Selenium;
using SeleniumExtras.WaitHelpers;
using System;
using System.Collections.Generic;
using System.Text;

namespace AppointmentSystemTests.HeroAuthCoice
{
    public class SignInTests:Base
    {
        [Test]
        public void SignInFromAuthChoice_ShouldOpenLoginModal()
        {
            driver.FindElement(By.CssSelector("[data-testid='book-now-btn']")).Click();

            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='login-btn']"))).Click();

            var authModal = wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='auth-modal']")));

            Assert.That(authModal.Displayed, Is.True);
        }

        [Test]
        public void SuccessfulLogin_ShouldRedirectToBooking()
        {
            driver.FindElement(By.CssSelector("[data-testid='book-now-btn']")).Click();

            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='login-btn']"))).Click();

            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='email-input']"))).SendKeys("rosella.dare@example.org");

            driver.FindElement(By.CssSelector("[data-testid='password-input']")).SendKeys("password");

            driver.FindElement(By.CssSelector("[data-testid='login-submit']")).Click();

            wait.Until(ExpectedConditions.UrlContains("/booking"));
        }

        [Test]
        public void SignInThroughHeaderShouldShowToast()
        {
            driver.FindElement(By.CssSelector("[data-testid='headerbtn']")).Click();

            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='email-input']"))).SendKeys("rosella.dare@example.org");

            driver.FindElement(By.CssSelector("[data-testid='password-input']")).SendKeys("password");

            driver.FindElement(By.CssSelector("[data-testid='login-submit']")).Click();

            var toast = wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='toast']")));

            Assert.That(toast.Text, Does.Contain("Successfully signed in"));
        }

        [Test]
        public void WhenSignedInBookBtnShouldNavigateToBookingPage()
        {
            driver.FindElement(By.CssSelector("[data-testid='headerbtn']")).Click();

            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='email-input']"))).SendKeys("rosella.dare@example.org");

            driver.FindElement(By.CssSelector("[data-testid='password-input']")).SendKeys("password");

            driver.FindElement(By.CssSelector("[data-testid='login-submit']")).Click();

            driver.FindElement(By.CssSelector("[data-testid='book-now-btn']")).Click();

            wait.Until(ExpectedConditions.UrlContains("/booking"));
        }

        [TestCase("email")]
        [TestCase("email@")]
        [TestCase("@email")]
        [TestCase("@")]
        [Test]
        public void SignInWithNotValidEmailFormatShouldThrowError(string email)
        {
            driver.FindElement(By.CssSelector("[data-testid='headerbtn']")).Click();

            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='email-input']"))).SendKeys(email);

            driver.FindElement(By.CssSelector("[data-testid='password-input']")).SendKeys("password");

            driver.FindElement(By.CssSelector("[data-testid='login-submit']")).Click();

            var errorMessage = wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='errormsg-login']")));
            string errorText = errorMessage.Text;

            Assert.That(errorText.Contains("The email field must be a valid email address."), Is.True);
        }

        [Test]
        public void SignInWithWrongCredentialsWillThrowError()
        {
            driver.FindElement(By.CssSelector("[data-testid='headerbtn']")).Click();

            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='email-input']"))).SendKeys("notregisteredemail@gmail.com");

            driver.FindElement(By.CssSelector("[data-testid='password-input']")).SendKeys("notregisteredpass");

            driver.FindElement(By.CssSelector("[data-testid='login-submit']")).Click();

            var errorMessage = wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='errormsg-login']")));
            string errorText = errorMessage.Text;

            Assert.That(errorText.Contains("Something went wrong"), Is.True);
        }

        [Test]
        public void SignInWithEmptyFieldsWillThrowError()
        {
            driver.FindElement(By.CssSelector("[data-testid='headerbtn']")).Click();

            driver.FindElement(By.CssSelector("[data-testid='login-submit']")).Click();

            var errorMessage = wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='errormsg-login']")));
            string errorText = errorMessage.Text;

            Assert.That(errorText.Contains("The email field is required."), Is.True);
        }

        [Test]
        public void SignInWithEmptyPasswordFieldWillThrowError()
        {
            driver.FindElement(By.CssSelector("[data-testid='headerbtn']")).Click();

            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='email-input']"))).SendKeys("email@email.com");

            driver.FindElement(By.CssSelector("[data-testid='login-submit']")).Click();

            var errorMessage = wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='errormsg-login']")));
            string errorText = errorMessage.Text;

            Assert.That(errorText.Contains("The password field is required."), Is.True);
        }
    }
}
