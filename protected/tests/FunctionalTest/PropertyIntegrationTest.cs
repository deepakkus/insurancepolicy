using Microsoft.VisualStudio.TestTools.UnitTesting;
using OpenQA.Selenium;
using OpenQA.Selenium.Chrome;
using OpenQA.Selenium.Firefox;
using OpenQA.Selenium.IE;
using OpenQA.Selenium.Interactions;
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
    public class PropertyIntegrationTest
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
        public void GetClient()
        {
            driver.Manage().Window.Maximize();
            driver.Manage().Cookies.DeleteAllCookies();
            driver.Navigate().GoToUrl(appURL);
            driver.FindElement(By.Name("LoginForm[username]")).SendKeys("testciuser");
            driver.FindElement(By.Name("LoginForm[password]")).SendKeys("testcipassword");
            //driver.FindElement(By.Name("LoginForm[username]")).SendKeys("kewat");
            //driver.FindElement(By.Name("LoginForm[password]")).SendKeys("kewat");
            driver.FindElement(By.XPath("//input[@type='submit']")).Click();

            IWebElement ele1 = waitForPageUntilElementIsVisible(By.XPath("(//span[@class='caret'])[3]"), 60);
            driver.Manage().Timeouts().ImplicitWait = TimeSpan.FromSeconds(10);
            driver.FindElement(By.XPath("(//span[@class='caret'])[3]")).Click();
            driver.Manage().Timeouts().ImplicitWait = TimeSpan.FromSeconds(10);
            driver.FindElement(By.XPath("//a[text()='Clients']")).Click();
            driver.Manage().Timeouts().ImplicitWait = TimeSpan.FromSeconds(10);
            IWebElement ele2 = waitForPageUntilElementIsVisible(By.XPath("//a[text()='Add New Client']"), 60);
            driver.FindElement(By.XPath("//a[text()='Add New Client']")).Click();

            driver.Manage().Timeouts().ImplicitWait = TimeSpan.FromSeconds(10);
            IWebElement ele3 = waitForPageUntilElementIsVisible(By.XPath("//input[@type='submit']"), 60);
            driver.FindElement(By.Name("Client[name]")).SendKeys("CITest");
            driver.FindElement(By.Name("Client[code]")).SendKeys("CI");
            driver.FindElement(By.Id("Client_wds_fire")).Click();
            driver.FindElement(By.Id("Client_wds_risk")).Click();
            driver.FindElement(By.Id("Client_wds_pro")).Click();
            driver.FindElement(By.Id("Client_wds_education")).Click();
            driver.FindElement(By.Id("Client_api")).Click();

            var element = driver.FindElement(By.XPath("//input[@type='submit']"));
            Actions actions = new Actions(driver);
            actions.MoveToElement(element);
            actions.Perform();

            driver.FindElement(By.XPath("//input[@type='submit']")).Click();

            IWebElement ele = waitForPageUntilElementIsVisible(By.XPath("(//table[@class='items'])//td[text()='CITest']"), 60);
            //IWebElement ele = waitForPageUntilElementIsVisible(By.XPath("(//table[@class='items'])//td[text()='USAA']"), 60);

            Assert.IsNotNull(ele);
        }

        public IWebElement waitForPageUntilElementIsVisible(By locator, int maxSeconds)
        {
            return new WebDriverWait(driver, TimeSpan.FromSeconds(maxSeconds)).Until(ExpectedConditions.ElementIsVisible(locator));
        }

        [TestCleanup()]
        public void MyTestCleanup()
        {
            driver.Quit();
        }
    }

    internal class SelectElement
    {
        private IWebElement webElement;

        public SelectElement(IWebElement webElement)
        {
            this.webElement = webElement;
        }
    }
}