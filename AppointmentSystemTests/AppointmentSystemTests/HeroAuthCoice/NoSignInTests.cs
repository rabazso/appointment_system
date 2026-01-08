using OpenQA.Selenium;
using SeleniumExtras.WaitHelpers;
using System;
using System.Collections.Generic;
using System.Text;

namespace AppointmentSystemTests.HeroAuthCoice
{
    public class NoSignInTests : Base
    {
        [Test]
        public void BookNow_WhenNotLoggedIn_ShouldShowAuthChoiceModal()
        {
            driver.FindElement(By.CssSelector("[data-testid='book-now-btn']")).Click();

            var guestButton = wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='guest-btn']")));

            Assert.That(guestButton.Displayed, Is.True);
        }

        [Test]
        public void SignInBtnThroughHeaderShouldShowAuthChoiceModal()
        {
            driver.FindElement(By.CssSelector("[data-testid='headerbtn']")).Click();

            var modal = wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='auth-modal']")));

            Assert.That(modal.Displayed, Is.True);
        }

        [Test]
        public void ContinueAsGuest_ShouldNavigateToBooking()
        {
            driver.FindElement(By.CssSelector("[data-testid='book-now-btn']")).Click();

            wait.Until(ExpectedConditions.ElementIsVisible(By.CssSelector("[data-testid='guest-btn']"))).Click();

            wait.Until(ExpectedConditions.UrlContains("/booking"));

            Assert.That(driver.Url, Does.Contain("/booking"));
        }
    }
}
