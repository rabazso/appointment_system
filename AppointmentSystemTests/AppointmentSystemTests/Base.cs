using OpenQA.Selenium;
using OpenQA.Selenium.Chrome;
using OpenQA.Selenium.Support.UI;
using NUnit.Framework;
using SeleniumExtras.WaitHelpers;

namespace AppointmentSystemTests
{
    public class Base
    {
        protected IWebDriver driver = null!;
        protected WebDriverWait wait = null!;

        protected const string BaseUrl = "http://frontend.vm1.test";
        protected const string CustomerEmail = "gregory.kunze@example.org";
        protected const string CustomerPassword = "password";

        [SetUp]
        public void Setup()
        {
            var options = new ChromeOptions();
            options.AddArgument("--start-maximized");
            options.AddArgument("--no-sandbox");
            options.AddArgument("--disable-dev-shm-usage");

            driver = new ChromeDriver(options);
            driver.Manage().Window.Maximize();

            wait = new WebDriverWait(driver, TimeSpan.FromSeconds(8));
            driver.Navigate().GoToUrl(BaseUrl);
        }

        [TearDown]
        public void TearDown()
        {
            driver?.Quit();
            driver?.Dispose();
        }

        protected IWebElement ClickTestId(string testId)
        {
            var element = Wait.Until(ExpectedConditions.ElementToBeClickable(TestId(testId)));
            element.Click();

            return element;
        }

        protected IWebElement TypeTestId(string testId, string value)
        {
            var element = Wait.Until(ExpectedConditions.ElementIsVisible(TestId(testId)));
            element.Clear();
            element.SendKeys(value);

            return element;
        }

        protected IWebElement VisibleTestId(string testId)
        {
            return Wait.Until(ExpectedConditions.ElementIsVisible(TestId(testId)));
        }

        protected void OpenLoginFromHeader()
        {
            ClickTestId("headerbtn");
            VisibleTestId("auth-modal");
        }

        protected void LoginAsCustomer()
        {
            TypeTestId("email-input", CustomerEmail);
            TypeTestId("password-input", CustomerPassword);
            ClickTestId("signin-submit");
        }

        protected IWebElement WaitForValidationMessage()
        {
            return Wait.Until(currentDriver =>
                currentDriver
                    .FindElements(By.CssSelector(".formkit-messages, [data-testid='errormsg-login'], [data-testid='errormsg-signup']"))
                    .FirstOrDefault(element => element.Displayed && !string.IsNullOrWhiteSpace(element.Text)));
        }

        protected string UniqueEmail()
        {
            return $"selenium-{Guid.NewGuid():N}@example.test";
        }

        protected static By TestId(string testId)
        {
            return By.CssSelector($"[data-testid='{testId}']");
        }

        private WebDriverWait Wait
        {
            get
            {
                return wait;
            }
        }
    }
}
