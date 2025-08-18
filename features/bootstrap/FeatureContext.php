<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use GryfOSS\Odds\Odds;
use GryfOSS\Odds\OddsFactory;
use GryfOSS\Odds\OddsLadder;
use GryfOSS\Odds\CustomOddsLadder;
use GryfOSS\Odds\Utils\OddsLadder as UtilsOddsLadder;
use GryfOSS\Odds\Exception\InvalidPriceException;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    private ?OddsFactory $oddsFactory = null;
    private ?Odds $odds = null;
    private ?\Exception $lastException = null;
    private string $currentLadderType = 'none';

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        require_once __DIR__ . '/../../vendor/autoload.php';
    }

    /**
     * @Given I have an odds factory
     */
    public function iHaveAnOddsFactory()
    {
        $this->oddsFactory = new OddsFactory();
        $this->currentLadderType = 'none';
    }

    /**
     * @Given I have an odds factory with standard odds ladder
     */
    public function iHaveAnOddsFactoryWithStandardOddsLadder()
    {
        $this->oddsFactory = new OddsFactory(new OddsLadder());
        $this->currentLadderType = 'standard';
    }

    /**
     * @Given I have an odds factory with custom odds ladder
     */
    public function iHaveAnOddsFactoryWithCustomOddsLadder()
    {
        $this->oddsFactory = new OddsFactory(new CustomOddsLadder());
        $this->currentLadderType = 'custom';
    }

    /**
     * @Given I have an odds factory with utils odds ladder
     */
    public function iHaveAnOddsFactoryWithUtilsOddsLadder()
    {
        $this->oddsFactory = new OddsFactory(new UtilsOddsLadder());
        $this->currentLadderType = 'utils';
    }

    /**
     * @When I create odds from decimal :decimal
     */
    public function iCreateOddsFromDecimal(string $decimal)
    {
        try {
            $this->odds = $this->oddsFactory->fromDecimal($decimal);
            $this->lastException = null;
        } catch (\Exception $e) {
            $this->odds = null;
            $this->lastException = $e;
        }
    }

    /**
     * @When I create odds from fractional :numerator/:denominator
     */
    public function iCreateOddsFromFractional(int $numerator, int $denominator)
    {
        try {
            $this->odds = $this->oddsFactory->fromFractional($numerator, $denominator);
            $this->lastException = null;
        } catch (\Exception $e) {
            $this->odds = null;
            $this->lastException = $e;
        }
    }

    /**
     * @When I create odds from moneyline :moneyline
     */
    public function iCreateOddsFromMoneyline(string $moneyline)
    {
        try {
            $this->odds = $this->oddsFactory->fromMoneyline($moneyline);
            $this->lastException = null;
        } catch (\Exception $e) {
            $this->odds = null;
            $this->lastException = $e;
        }
    }

    /**
     * @When I directly create odds with decimal :decimal, fractional :fractional and moneyline :moneyline
     */
    public function iDirectlyCreateOddsWithDecimalFractionalAndMoneyline(string $decimal, string $fractional, string $moneyline)
    {
        try {
            $this->odds = new Odds($decimal, $fractional, $moneyline);
            $this->lastException = null;
        } catch (\Exception $e) {
            $this->odds = null;
            $this->lastException = $e;
        }
    }

    /**
     * @Then the decimal odds should be :expected
     */
    public function theDecimalOddsShouldBe(string $expected)
    {
        Assert::assertNotNull($this->odds, 'Odds object should not be null');
        Assert::assertEquals($expected, $this->odds->getDecimal());
    }

    /**
     * @Then the fractional odds should be :expected
     */
    public function theFractionalOddsShouldBe(string $expected)
    {
        Assert::assertNotNull($this->odds, 'Odds object should not be null');
        Assert::assertEquals($expected, $this->odds->getFractional());
    }

    /**
     * @Then the moneyline odds should be :expected
     */
    public function theMoneylineOddsShouldBe(string $expected)
    {
        Assert::assertNotNull($this->odds, 'Odds object should not be null');
        Assert::assertEquals($expected, $this->odds->getMoneyline());
    }

    /**
     * @Then the probability should be :expected
     */
    public function theProbabilityShouldBe(string $expected)
    {
        Assert::assertNotNull($this->odds, 'Odds object should not be null');
        Assert::assertEquals($expected, $this->odds->getProbability());
    }

    /**
     * @Then an InvalidPriceException should be thrown
     */
    public function anInvalidPriceExceptionShouldBeThrown()
    {
        Assert::assertInstanceOf(InvalidPriceException::class, $this->lastException);
    }

    /**
     * @Then an InvalidPriceException should be thrown with message :message
     */
    public function anInvalidPriceExceptionShouldBeThrownWithMessage(string $message)
    {
        Assert::assertInstanceOf(InvalidPriceException::class, $this->lastException);
        Assert::assertStringContainsString($message, $this->lastException->getMessage());
    }

    /**
     * @Then the odds should be successfully created
     */
    public function theOddsShouldBeSuccessfullyCreated()
    {
        Assert::assertNotNull($this->odds, 'Odds object should have been created successfully');
        Assert::assertNull($this->lastException, 'No exception should have been thrown');
    }

    /**
     * @Then all values should be strings
     */
    public function allValuesShouldBeStrings()
    {
        Assert::assertNotNull($this->odds, 'Odds object should not be null');
        Assert::assertIsString($this->odds->getDecimal());
        Assert::assertIsString($this->odds->getFractional());
        Assert::assertIsString($this->odds->getMoneyline());
        Assert::assertIsString($this->odds->getProbability());
    }

    /**
     * @Given I have the following conversion scenarios:
     */
    public function iHaveTheFollowingConversionScenarios(TableNode $table)
    {
        foreach ($table->getColumnsHash() as $row) {
            $this->iCreateOddsFromDecimal($row['decimal']);
            if (isset($row['fractional'])) {
                $this->theFractionalOddsShouldBe($row['fractional']);
            }
            if (isset($row['moneyline'])) {
                $this->theMoneylineOddsShouldBe($row['moneyline']);
            }
            if (isset($row['probability'])) {
                $this->theProbabilityShouldBe($row['probability']);
            }
        }
    }

    /**
     * @Given I test the following fractional to decimal conversions:
     */
    public function iTestTheFollowingFractionalToDecimalConversions(TableNode $table)
    {
        foreach ($table->getColumnsHash() as $row) {
            $parts = explode('/', $row['fractional']);
            $numerator = (int)$parts[0];
            $denominator = (int)$parts[1];

            $this->iCreateOddsFromFractional($numerator, $denominator);
            $this->theDecimalOddsShouldBe($row['decimal']);
            if (isset($row['moneyline'])) {
                $this->theMoneylineOddsShouldBe($row['moneyline']);
            }
        }
    }

    /**
     * @Given I test the following moneyline to decimal conversions:
     */
    public function iTestTheFollowingMoneylineToDecimalConversions(TableNode $table)
    {
        foreach ($table->getColumnsHash() as $row) {
            $this->iCreateOddsFromMoneyline($row['moneyline']);
            $this->theDecimalOddsShouldBe($row['decimal']);
            if (isset($row['fractional'])) {
                $this->theFractionalOddsShouldBe($row['fractional']);
            }
        }
    }

    /**
     * @Then the fractional odds should match the :ladderType ladder expectations
     */
    public function theFractionalOddsShouldMatchTheLadderExpectations(string $ladderType)
    {
        Assert::assertEquals($ladderType, $this->currentLadderType, "Current ladder type should match expected");
        Assert::assertNotNull($this->odds, 'Odds object should not be null');

        // Verify that fractional odds are in expected format
        $fractional = $this->odds->getFractional();
        Assert::assertMatchesRegularExpression('/^\d+\/\d+$/', $fractional, 'Fractional should be in format n/d');
    }

    /**
     * @When I reset the odds factory
     */
    public function iResetTheOddsFactory()
    {
        $this->oddsFactory = null;
        $this->odds = null;
        $this->lastException = null;
        $this->currentLadderType = 'none';
    }
}
