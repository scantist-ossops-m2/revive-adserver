<?php

/*
+---------------------------------------------------------------------------+
| Revive Adserver                                                           |
| http://www.revive-adserver.com                                            |
|                                                                           |
| Copyright: See the COPYRIGHT.txt file.                                    |
| License: GPLv2 or later, see the LICENSE.txt file.                        |
+---------------------------------------------------------------------------+
*/

require_once MAX_PATH . '/lib/OA/Dal.php';
require_once MAX_PATH . '/lib/max/Dal/tests/util/DalUnitTestCase.php';
require_once MAX_PATH . '/lib/OA/Dll.php';

/**
 * A class for testing DAL Banners methods
 *
 * @package    MaxDal
 * @subpackage TestSuite
 *
 */
class MAX_Dal_Admin_BannersTest extends DalUnitTestCase
{
    /** @var MAX_Dal_Admin_Banners */
    public $dalBanners;

    public function setUp()
    {
        $this->dalBanners = OA_Dal::factoryDAL('banners');
    }

    public function tearDown()
    {
        DataGenerator::cleanUp();
    }

    public function testGetAllBanners()
    {
        // Insert banners
        $numBanners = 2;
        $doBanners = OA_Dal::factoryDO('banners');
        $doBanners->acls_updated = '2007-04-03 18:39:45';
        DataGenerator::generate($doBanners, $numBanners);

        // Call method
        $aBanners = $this->dalBanners->getAllBanners('name', 'up');

        // Test same number of banners are returned.
        $this->assertEqual(count($aBanners), $numBanners);
    }

    public function testGetAllBannersUnderAgency()
    {
        $doClients = OA_Dal::factoryDO('clients');
        $doClients->agencyid = 0;
        $doClients->reportlastdate = '2007-04-03 18:39:45';
        $aClientId[] = DataGenerator::generateOne($doClients);

        $doClients = OA_Dal::factoryDO('clients');
        $doClients->agencyid = 1;
        $doClients->reportlastdate = '2007-04-03 18:39:45';
        $aClientId[] = DataGenerator::generateOne($doClients);

        $doCampaigns = OA_Dal::factoryDO('campaigns');
        $doCampaigns->clientid = $aClientId[0];
        $aCampaignId[] = DataGenerator::generateOne($doCampaigns);

        $doCampaigns = OA_Dal::factoryDO('campaigns');
        $doCampaigns->clientid = $aClientId[1];
        $aCampaignId[] = DataGenerator::generateOne($doCampaigns);

        $doBanners = OA_Dal::factoryDO('banners');
        $doBanners->campaignid = $aCampaignId[0];
        $doBanners->acls_updated = '2007-04-03 18:39:45';
        DataGenerator::generateOne($doBanners);

        $doBanners = OA_Dal::factoryDO('banners');
        $doBanners->campaignid = $aCampaignId[1];
        $doBanners->acls_updated = '2007-04-03 18:39:45';
        DataGenerator::generateOne($doBanners);

        $aBanners = $this->dalBanners->getAllBannersUnderAgency(1, 'name', 'up');

        $this->assertEqual(count($aBanners), 1);
    }


    public function testGetAllBannersUnderCampaign()
    {
        $doBanners = OA_Dal::factoryDO('banners');
        $doBanners->campaignid = 0;
        $doBanners->acls_updated = '2007-04-03 18:39:45';
        DataGenerator::generateOne($doBanners);

        $doBanners = OA_Dal::factoryDO('banners');
        $doBanners->campaignid = 0;
        $doBanners->acls_updated = '2007-04-03 18:39:45';
        DataGenerator::generateOne($doBanners);

        $doBanners = OA_Dal::factoryDO('banners');
        $doBanners->campaignid = 1;
        $doBanners->acls_updated = '2007-04-03 18:39:45';
        DataGenerator::generateOne($doBanners);

        $aBanners = $this->dalBanners->getAllBannersUnderCampaign(1, 'name', 'up');
        $this->assertEqual(count($aBanners), 1);
    }

    public function testCountActiveBanners()
    {
        // Insert an active campaign
        $doCampaigns = OA_Dal::factoryDO('campaigns');
        $doCampaigns->status = OA_ENTITY_STATUS_RUNNING;
        $activeCampaignId = DataGenerator::generateOne($doCampaigns);

        // Insert an active banner
        $doBanners = OA_Dal::factoryDO('banners');
        $doBanners->status = OA_ENTITY_STATUS_RUNNING;
        $doBanners->campaignid = $activeCampaignId;
        $doBanners->acls_updated = '2007-04-03 18:39:45';
        $activeBannerId = DataGenerator::generateOne($doBanners);

        // Insert an inactive banner
        $doBanners = OA_Dal::factoryDO('banners');
        $doBanners->status = OA_ENTITY_STATUS_PAUSED;
        $doBanners->campaignid = $activeCampaignId;
        $doBanners->acls_updated = '2007-04-03 18:39:45';
        $inactiveBannerId = DataGenerator::generateOne($doBanners);

        // Count the active banners
        $expected = 1;
        $activeCount = $this->dalBanners->countActiveBanners();
        $this->assertEqual($activeCount, $expected);
    }

    public function testCountActiveBannersUnderAgency()
    {
        $agencyId = 1;

        // Insert an advertiser under this agency.
        $doClients = OA_Dal::factoryDO('clients');
        $doClients->agencyid = $agencyId;
        $doClients->reportlastdate = '2007-04-03 18:39:45';
        $agencyClientId = DataGenerator::generateOne($doClients);

        // Insert an active campaign with this client
        $doCampaigns = OA_Dal::factoryDO('campaigns');
        $doCampaigns->status = OA_ENTITY_STATUS_RUNNING;
        $doCampaigns->clientid = $agencyClientId;
        $agencyCampaignIdActive = DataGenerator::generateOne($doCampaigns);

        // Insert an active banner in this campaign
        $doBanners = OA_Dal::factoryDO('banners');
        $doBanners->status = OA_ENTITY_STATUS_RUNNING;
        $doBanners->campaignid = $agencyCampaignIdActive;
        $doBanners->acls_updated = '2007-04-03 18:39:45';
        $agencyBannerIdActive = DataGenerator::generateOne($doBanners);

        // Insert an inactive banner in this campaign
        $doBanners = OA_Dal::factoryDO('banners');
        $doBanners->status = OA_ENTITY_STATUS_PAUSED;
        $doBanners->campaignid = $agencyCampaignIdActive;
        $doBanners->acls_updated = '2007-04-03 18:39:45';
        $agencyBannerIdInactive = DataGenerator::generateOne($doBanners);

        // Insert an advertiser under no agency.
        $doClients = OA_Dal::factoryDO('clients');
        $doClients->agencyid = 0;
        $doClients->reportlastdate = '2007-04-03 18:39:45';
        $noAgencyClientId = DataGenerator::generateOne($doClients);

        // Insert an active campaign with this client
        $doCampaigns = OA_Dal::factoryDO('campaigns');
        $doCampaigns->status = OA_ENTITY_STATUS_RUNNING;
        $doCampaigns->clientid = $noAgencyClientId;
        $noAgencyCampaignIdActive = DataGenerator::generateOne($doCampaigns);

        // Insert an active banner in this campaign
        $doBanners = OA_Dal::factoryDO('banners');
        $doBanners->status = OA_ENTITY_STATUS_RUNNING;
        $doBanners->campaignid = $noAgencyCampaignIdActive;
        $doBanners->acls_updated = '2007-04-03 18:39:45';
        $noAgencyBannerIdActive = DataGenerator::generateOne($doBanners);

        // Count the active banners
        $expected = 1;
        $activeCount = $this->dalBanners->countActiveBannersUnderAgency($agencyId);

        $this->assertEqual($activeCount, $expected);
    }

    public function testGetBannerByKeyword()
    {
        // Search for banners when none exist
        $expected = 0;
        $rsBanners = $this->dalBanners->getBannerByKeyword('foo');
        $rsBanners->find();
        $actual = $rsBanners->getRowCount();
        $this->assertEqual($actual, $expected);

        $agencyId = 1;
        $rsBanners = $this->dalBanners->getBannerByKeyword('foo', $agencyId);
        $rsBanners->find();
        $actual = $rsBanners->getRowCount();
        $this->assertEqual($actual, $expected);

        // Insert a banner (and it's parent campaign/client)
        $doBanners = OA_Dal::factoryDO('banners');
        $doBanners->description = 'foo';
        $doBanners->alt = 'bar';
        $doBanners->acls_updated = '2007-04-03 18:39:45';
        $aData = [
            'reportlastdate' => ['2007-04-03 18:39:45']
        ];

        DataGenerator::setData('clients', $aData);
        $bannerId = DataGenerator::generate($doBanners, 1, true);
        $agencyId = DataGenerator::getReferenceId('agency');

        // Search for banner by description
        $expected = 1;
        $rsBanners = $this->dalBanners->getBannerByKeyword('foo');
        $rsBanners->find();
        $actual = $rsBanners->getRowCount();
        $this->assertEqual($actual, $expected);

        // Search for banner by alt
        $expected = 1;
        $rsBanners = $this->dalBanners->getBannerByKeyword('bar');
        $rsBanners->find();
        $actual = $rsBanners->getRowCount();
        $this->assertEqual($actual, $expected);

        // Restrict to agency ID (client was created with default agency ID of 1)
        $expected = 1;
        $rsBanners = $this->dalBanners->getBannerByKeyword('bar', $agencyId);
        $rsBanners->find();
        $actual = $rsBanners->getRowCount();
        $this->assertEqual($actual, $expected);
    }

    public function testMoveBannerToCampaign()
    {
        // Insert a banner
        $doBanners = OA_Dal::factoryDO('banners');
        $doBanners->acls_updated = '2007-04-03 18:39:45';
        $bannerId = DataGenerator::generateOne($doBanners);

        // Move it
        $newCampaignId = 99;
        $this->assertTrue($this->dalBanners->moveBannerToCampaign($bannerId, $newCampaignId));

        // Check its campaign ID
        $doBanners = OA_Dal::staticGetDO('banners', $bannerId);
        $this->assertEqual($doBanners->campaignid, $newCampaignId);
    }

    public function testGetBannersCampaignsClients()
    {
        // Insert 2 banners
        $doBanners = OA_Dal::factoryDO('banners');
        $doBanners->acls_updated = '2007-04-03 18:39:45';
        $aData = [
            'reportlastdate' => ['2007-04-03 18:39:45']
        ];

        DataGenerator::setData('clients', $aData);
        $aBannerIds = DataGenerator::generate($doBanners, 2, true);

        // Check the correct number of rows returned
        $expectedRows = 2;
        $rsBanners = $this->dalBanners->getBannersCampaignsClients();
        $rsBanners->find();
        $actualRows = $rsBanners->getRowCount();
        $this->assertEqual($actualRows, $expectedRows);

        // Check each row has the correct number of fields
        $rsBanners->fetch();
        $aBanner = $rsBanners->export();
        $this->assertEqual(count($aBanner), 6);
    }
}
