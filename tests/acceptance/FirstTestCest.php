<?php


class FirstTestCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function _after(AcceptanceTester $I)
    {
    }

    // tests
    public function tryToTest(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->see('Registration');
        $I->fillField('#signin-form input[name="login"]', 'customer');
        $I->fillField('#signin-form input[name="pass"]', 'Test11');
        $I->click('Sing In');
        $I->see('My orders');
    }
}
