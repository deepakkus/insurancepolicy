using Microsoft.VisualStudio.TestTools.UnitTesting;
using OpenQA.Selenium;
using OpenQA.Selenium.Chrome;
using OpenQA.Selenium.Firefox;
using OpenQA.Selenium.IE;
using OpenQA.Selenium.Support.UI;
using SeleniumExtras.WaitHelpers;
using System;
using System.Collections.Generic;
using System.IO;
using System.Reflection;
using System.Text;

namespace FunctionalTest
{
    [TestClass]
    public class AuthenticationTest
    {
        private IWebDriver driver;
        private string appURL;

        [TestInitialize()]
        public void SetupTest()
        {
             appURL = "http://vmwds-ci-a1.excellimatrix.local:85/index.php?r=site/login";
             //appURL = "http://localhost:1115/index.php?r=site/login";

            string browser = "Chrome";
            switch (browser)
            {
                case "Chrome":
                    driver = new ChromeDriver(Path.GetDirectoryName(Assembly.GetExecutingAssembly().Location));
                    break;
                case "Firefox":
                    driver = new FirefoxDriver();
                    break;
                case "IE":
                    driver = new InternetExplorerDriver();
                    break;
                default:
                    driver = new ChromeDriver();
                    break;
            }
        }

        [TestMethod]
        [TestCategory("Chrome")]
        public void AuthenticateUserLogin()
        {
            driver.Manage().Cookies.DeleteAllCookies();
            driver.Navigate().GoToUrl(appURL);
            //driver.FindElement(By.Name("LoginForm[username]")).SendKeys("kewat");
            //driver.FindElement(By.Name("LoginForm[password]")).SendKeys("kewat");
            driver.FindElement(By.Name("LoginForm[username]")).SendKeys("testciuser");
            driver.FindElement(By.Name("LoginForm[password]")).SendKeys("testcipassword");
            driver.FindElement(By.XPath("//input[@type='submit']")).Click();
            IWebElement element = waitForPageUntilElementIsVisible(By.XPath("//a[text()='WDS Admin']"), 60);
            Assert.IsNotNull(element);
        }

        public IWebElement waitForPageUntilElementIsVisible(By locator, int maxSeconds)
        {
            return new WebDriverWait(driver, TimeSpan.FromSeconds(maxSeconds)).Until(ExpectedConditions.ElementIsVisible(locator));
        }


        [TestMethod]
        [TestCategory("Chrome")]
        public void AuthenticateUserLoginError()
        {
            driver.Manage().Cookies.DeleteAllCookies();
            driver.Navigate().GoToUrl(appURL);
            driver.FindElement(By.Name("LoginForm[username]")).Clear();
            driver.FindElement(By.Name("LoginForm[password]")).Clear();
            driver.FindElement(By.XPath("//input[@type='submit']")).Click();
            IWebElement element = waitForPageUntilElementIsVisible(By.XPath("//div[@class='errorMessage']"), 30);
            Assert.AreEqual("Username cannot be blank.", driver.FindElement(By.XPath("//div[@class='errorMessage']")).Text);
        }

        [TestCleanup()]
        public void MyTestCleanup()
        {
            driver.Quit();
        }
    }

}
