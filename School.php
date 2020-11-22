<?php
namespace Netz\Edvisia\Domain\Model;


/***
 *
 * This file is part of the "EDVISIA" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2020 Saurav Dalai
 *
 ***/
/**
 * School
 */
class School extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{



    /**
     * schoolId
     * 
     * @var string
     */
    protected $schoolIds = '';

    /**
     * lastImport
     * 
     * @var int
     */
    protected $lastImport = 0;

    /**
     * ispremium
     * 
     * @var int
     */
    protected $ispremium = 0;

    /**
     * title
     * 
     * @var string
     */
    protected $titles = '';

    /**
     * street
     * 
     * @var string
     */
    protected $street = '';

    /**
     * zip
     * 
     * @var string
     */
    protected $zips = '';

    /**
     * city
     * 
     * @var string
     */
    protected $city = '';

    /**
     * state
     * 
     * @var string
     */
    protected $state = '';

    /**
     * phone
     * 
     * @var string
     */
    protected $phone = '';

    /**
     * fax
     * 
     * @var string
     */
    protected $fax = '';

    /**
     * website
     * 
     * @var string
     */
    protected $website = '';

    /**
     * email
     * 
     * @var string
     */
    protected $email = '';

    /**
     * headmaster
     * 
     * @var string
     */
    protected $headmaster = '';

    /**
     * contactperson
     * 
     * @var string
     */
    protected $contactperson = '';

    /**
     * responsiblebody
     * 
     * @var string
     */
    protected $responsiblebody = '';

    /**
     * numberstudents
     * 
     * @var int
     */
    protected $numberstudents = 0;

    /**
     * numberteachers
     * 
     * @var int
     */
    protected $numberteachers = 0;

    /**
     * monthlycharge
     * 
     * @var string
     */
    protected $monthlycharge = '';

    /**
     * foundingyear
     * 
     * @var int
     */
    protected $foundingyear = 0;

    /**
     * languageorder
     * 
     * @var string
     */
    protected $languageorder = '';

    /**
     * electivemodules
     * 
     * @var string
     */
    protected $electivemodules = '';

    /**
     * description
     * 
     * @var string
     */
    protected $description = '';

  
     /**
     * images
     * 
     * @var  \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Cascade("remove")
     */
    protected $images = null;


    /**
     * field1
     * 
     * @var \Netz\Edvisia\Domain\Model\Field
     */
    protected $field1 = null;

    /**
     * field2
     * 
     * @var \Netz\Edvisia\Domain\Model\Field
     */
    protected $field2 = null;

    /**
     * field3
     * 
     * @var \Netz\Edvisia\Domain\Model\Field
     */
    protected $field3 = null;

    /**
     * field4
     * 
     * @var \Netz\Edvisia\Domain\Model\Field
     */
    protected $field4 = null;

    /**
     * field5
     * 
     * @var \Netz\Edvisia\Domain\Model\Field
     */
    protected $field5 = null;

    /**
     * status
     * 
     * @var \Netz\Edvisia\Domain\Model\Status
     */
    protected $status = null;

    /**
     * additionallanguage1
     * 
     * @var \Netz\Edvisia\Domain\Model\Language
     */
    protected $additionallanguage1 = null;

    /**
     * additionallanguage2
     * 
     * @var \Netz\Edvisia\Domain\Model\Language
     */
    protected $additionallanguage2 = null;

    /**
     * additionallanguage3
     * 
     * @var \Netz\Edvisia\Domain\Model\Language
     */
    protected $additionallanguage3 = null;

    /**
     * additionallanguage4
     * 
     * @var \Netz\Edvisia\Domain\Model\Language
     */
    protected $additionallanguage4 = null;

    /**
     * sports1
     * 
     * @var \Netz\Edvisia\Domain\Model\Sports
     */
    protected $sports1 = null;

    /**
     * sports2
     * 
     * @var \Netz\Edvisia\Domain\Model\Sports
     */
    protected $sports2 = null;

    /**
     * sports3
     * 
     * @var \Netz\Edvisia\Domain\Model\Sports
     */
    protected $sports3 = null;

    /**
     * sports4
     * 
     * @var \Netz\Edvisia\Domain\Model\Sports
     */
    protected $sports4 = null;

    /**
     * workinggroups1
     * 
     * @var \Netz\Edvisia\Domain\Model\Workinggroup
     */
    protected $workinggroups1 = null;

    /**
     * workinggroups2
     * 
     * @var \Netz\Edvisia\Domain\Model\Workinggroup
     */
    protected $workinggroups2 = null;

    /**
     * workinggroups3
     * 
     * @var \Netz\Edvisia\Domain\Model\Workinggroup
     */
    protected $workinggroups3 = null;

    /**
     * workinggroups4
     * 
     * @var \Netz\Edvisia\Domain\Model\Workinggroup
     */
    protected $workinggroups4 = null;

    



    /**
     * Returns the schoolId
     * 
     * @return string $schoolId
     */
    public function getSchoolId()
    {
        return $this->schoolId;
    }

    /**
     * Sets the schoolId
     * 
     * @param string $schoolId
     * @return void
     */
    public function setSchoolId($schoolId)
    {
        $this->schoolId = $schoolId;
    }

    /**
     * Returns the lastImport
     * 
     * @return int $lastImport
     */
    public function getLastImport()
    {
        return $this->lastImport;
    }

    /**
     * Sets the lastImport
     * 
     * @param int $lastImport
     * @return void
     */
    public function setLastImport($lastImport)
    {
        $this->lastImport = $lastImport;
    }

    /**
     * Returns the ispremium
     * 
     * @return int $ispremium
     */
    public function getIspremium()
    {
        return $this->ispremium;
    }

    /**
     * Sets the ispremium
     * 
     * @param int $ispremium
     * @return void
     */
    public function setIspremium($ispremium)
    {
        $this->ispremium = $ispremium;
    }

    /**
     * Returns the title
     * 
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title
     * 
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Returns the street
     * 
     * @return string $street
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Sets the street
     * 
     * @param string $street
     * @return void
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * Returns the zip
     * 
     * @return string $zip
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Sets the zip
     * 
     * @param string $zip
     * @return void
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }

    /**
     * Returns the city
     * 
     * @return string $city
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Sets the city
     * 
     * @param string $city
     * @return void
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * Returns the state
     * 
     * @return string $state
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Sets the state
     * 
     * @param string $state
     * @return void
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * Returns the phone
     * 
     * @return string $phone
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Sets the phone
     * 
     * @param string $phone
     * @return void
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * Returns the fax
     * 
     * @return string $fax
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Sets the fax
     * 
     * @param string $fax
     * @return void
     */
    public function setFax($fax)
    {
        $this->fax = $fax;
    }

    /**
     * Returns the field1
     * 
     * @return \Netz\Edvisia\Domain\Model\Field $field1
     */
    public function getField1()
    {
        return $this->field1;
    }

    /**
     * Sets the field1
     * 
     * @param \Netz\Edvisia\Domain\Model\Field $field1
     * @return void
     */
    public function setField1($field1)
    {
        $this->field1 = $field1;
    }

    /**
     * Returns the field2
     * 
     * @return \Netz\Edvisia\Domain\Model\Field $field2
     */
    public function getField2()
    {
        return $this->field2;
    }

    /**
     * Sets the field2
     * 
     * @param \Netz\Edvisia\Domain\Model\Field $field2
     * @return void
     */
    public function setField2($field2)
    {
        $this->field2 = $field2;
    }

    /**
     * Returns the field3
     * 
     * @return \Netz\Edvisia\Domain\Model\Field $field3
     */
    public function getField3()
    {
        return $this->field3;
    }

    /**
     * Sets the field3
     * 
     * @param \Netz\Edvisia\Domain\Model\Field $field3
     * @return void
     */
    public function setField3($field3)
    {
        $this->field3 = $field3;
    }

    /**
     * Returns the field4
     * 
     * @return \Netz\Edvisia\Domain\Model\Field $field4
     */
    public function getField4()
    {
        return $this->field4;
    }

    /**
     * Sets the field4
     * 
     * @param \Netz\Edvisia\Domain\Model\Field $field4
     * @return void
     */
    public function setField4($field4)
    {
        $this->field4 = $field4;
    }

    /**
     * Returns the field5
     * 
     * @return \Netz\Edvisia\Domain\Model\Field $field5
     */
    public function getField5()
    {
        return $this->field5;
    }

    /**
     * Sets the field5
     * 
     * @param \Netz\Edvisia\Domain\Model\Field $field5
     * @return void
     */
    public function setField5($field5)
    {
        $this->field5 = $field5;
    }

    /**
     * Returns the website
     * 
     * @return string $website
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Sets the website
     * 
     * @param string $website
     * @return void
     */
    public function setWebsite($website)
    {
        $this->website = $website;
    }

    /**
     * Returns the email
     * 
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets the email
     * 
     * @param string $email
     * @return void
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Returns the headmaster
     * 
     * @return string $headmaster
     */
    public function getHeadmaster()
    {
        return $this->headmaster;
    }

    /**
     * Sets the headmaster
     * 
     * @param string $headmaster
     * @return void
     */
    public function setHeadmaster($headmaster)
    {
        $this->headmaster = $headmaster;
    }

    /**
     * Returns the contactperson
     * 
     * @return string $contactperson
     */
    public function getContactperson()
    {
        return $this->contactperson;
    }

    /**
     * Sets the contactperson
     * 
     * @param string $contactperson
     * @return void
     */
    public function setContactperson($contactperson)
    {
        $this->contactperson = $contactperson;
    }

    /**
     * Returns the responsiblebody
     * 
     * @return string $responsiblebody
     */
    public function getResponsiblebody()
    {
        return $this->responsiblebody;
    }

    /**
     * Sets the responsiblebody
     * 
     * @param string $responsiblebody
     * @return void
     */
    public function setResponsiblebody($responsiblebody)
    {
        $this->responsiblebody = $responsiblebody;
    }

    /**
     * Returns the status
     * 
     * @return \Netz\Edvisia\Domain\Model\Status $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets the status
     * 
     * @param \Netz\Edvisia\Domain\Model\Status $status
     * @return void
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Returns the numberstudents
     * 
     * @return int $numberstudents
     */
    public function getNumberstudents()
    {
        return $this->numberstudents;
    }

    /**
     * Sets the numberstudents
     * 
     * @param int $numberstudents
     * @return void
     */
    public function setNumberstudents($numberstudents)
    {
        $this->numberstudents = $numberstudents;
    }

    /**
     * Returns the numberteachers
     * 
     * @return int $numberteachers
     */
    public function getNumberteachers()
    {
        return $this->numberteachers;
    }

    /**
     * Sets the numberteachers
     * 
     * @param int $numberteachers
     * @return void
     */
    public function setNumberteachers($numberteachers)
    {
        $this->numberteachers = $numberteachers;
    }

    /**
     * Returns the monthlycharge
     * 
     * @return string $monthlycharge
     */
    public function getMonthlycharge()
    {
        return $this->monthlycharge;
    }

    /**
     * Sets the monthlycharge
     * 
     * @param string $monthlycharge
     * @return void
     */
    public function setMonthlycharge($monthlycharge)
    {
        $this->monthlycharge = $monthlycharge;
    }

    /**
     * Returns the foundingyear
     * 
     * @return int $foundingyear
     */
    public function getFoundingyear()
    {
        return $this->foundingyear;
    }

    /**
     * Sets the foundingyear
     * 
     * @param int $foundingyear
     * @return void
     */
    public function setFoundingyear($foundingyear)
    {
        $this->foundingyear = $foundingyear;
    }

    /**
     * Returns the languageorder
     * 
     * @return string $languageorder
     */
    public function getLanguageorder()
    {
        return $this->languageorder;
    }

    /**
     * Sets the languageorder
     * 
     * @param string $languageorder
     * @return void
     */
    public function setLanguageorder($languageorder)
    {
        $this->languageorder = $languageorder;
    }

    /**
     * Returns the electivemodules
     * 
     * @return string $electivemodules
     */
    public function getElectivemodules()
    {
        return $this->electivemodules;
    }

    /**
     * Sets the electivemodules
     * 
     * @param string $electivemodules
     * @return void
     */
    public function setElectivemodules($electivemodules)
    {
        $this->electivemodules = $electivemodules;
    }

    /**
     * Returns the description
     * 
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the description
     * 
     * @param string $description
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

     
    /**
     * Returns the additionallanguage1
     * 
     * @return \Netz\Edvisia\Domain\Model\Language $additionallanguage1
     */
    public function getAdditionallanguage1()
    {
        return $this->additionallanguage1;
    }

    /**
     * Sets the additionallanguage1
     * 
     * @param \Netz\Edvisia\Domain\Model\Language $additionallanguage1
     * @return void
     */
    public function setAdditionallanguage1($additionallanguage1)
    {
        $this->additionallanguage1 = $additionallanguage1;
    }

    /**
     * Returns the additionallanguage2
     * 
     * @return \Netz\Edvisia\Domain\Model\Language $additionallanguage2
     */
    public function getAdditionallanguage2()
    {
        return $this->additionallanguage2;
    }

    /**
     * Sets the additionallanguage2
     * 
     * @param \Netz\Edvisia\Domain\Model\Language $additionallanguage2
     * @return void
     */
    public function setAdditionallanguage2($additionallanguage2)
    {
        $this->additionallanguage2 = $additionallanguage2;
    }

    /**
     * Returns the additionallanguage3
     * 
     * @return \Netz\Edvisia\Domain\Model\Language $additionallanguage3
     */
    public function getAdditionallanguage3()
    {
        return $this->additionallanguage3;
    }

    /**
     * Sets the additionallanguage3
     * 
     * @param \Netz\Edvisia\Domain\Model\Language $additionallanguage3
     * @return void
     */
    public function setAdditionallanguage3($additionallanguage3)
    {
        $this->additionallanguage3 = $additionallanguage3;
    }

    /**
     * Returns the additionallanguage4
     * 
     * @return \Netz\Edvisia\Domain\Model\Language $additionallanguage4
     */
    public function getAdditionallanguage4()
    {
        return $this->additionallanguage4;
    }

    /**
     * Sets the additionallanguage4
     * 
     * @param \Netz\Edvisia\Domain\Model\Language $additionallanguage4
     * @return void
     */
    public function setAdditionallanguage4($additionallanguage4)
    {
        $this->additionallanguage4 = $additionallanguage4;
    }

    /**
     * Returns the sports1
     * 
     * @return \Netz\Edvisia\Domain\Model\Sports $sports1
     */
    public function getSports1()
    {
        return $this->sports1;
    }

    /**
     * Sets the sports1
     * 
     * @param \Netz\Edvisia\Domain\Model\Sports $sports1
     * @return void
     */
    public function setSports1($sports1)
    {
        $this->sports1 = $sports1;
    }

    /**
     * Returns the sports2
     * 
     * @return \Netz\Edvisia\Domain\Model\Sports $sports2
     */
    public function getSports2()
    {
        return $this->sports2;
    }

    /**
     * Sets the sports2
     * 
     * @param \Netz\Edvisia\Domain\Model\Sports $sports2
     * @return void
     */
    public function setSports2($sports2)
    {
        $this->sports2 = $sports2;
    }

    /**
     * Returns the sports3
     * 
     * @return \Netz\Edvisia\Domain\Model\Sports $sports3
     */
    public function getSports3()
    {
        return $this->sports3;
    }

    /**
     * Sets the sports3
     * 
     * @param \Netz\Edvisia\Domain\Model\Sports $sports3
     * @return void
     */
    public function setSports3($sports3)
    {
        $this->sports3 = $sports3;
    }

    /**
     * Returns the sports4
     * 
     * @return \Netz\Edvisia\Domain\Model\Sports $sports4
     */
    public function getSports4()
    {
        return $this->sports4;
    }

    /**
     * Sets the sports4
     * 
     * @param \Netz\Edvisia\Domain\Model\Sports $sports4
     * @return void
     */
    public function setSports4($sports4)
    {
        $this->sports4 = $sports4;
    }

    /**
     * Returns the workinggroups1
     * 
     * @return \Netz\Edvisia\Domain\Model\Workinggroup $workinggroups1
     */
    public function getWorkinggroups1()
    {
        return $this->workinggroups1;
    }

    /**
     * Sets the workinggroups1
     * 
     * @param \Netz\Edvisia\Domain\Model\Workinggroup $workinggroups1
     * @return void
     */
    public function setWorkinggroups1($workinggroups1)
    {
        $this->workinggroups1 = $workinggroups1;
    }

    /**
     * Returns the workinggroups2
     * 
     * @return \Netz\Edvisia\Domain\Model\Workinggroup $workinggroups2
     */
    public function getWorkinggroups2()
    {
        return $this->workinggroups2;
    }

    /**
     * Sets the workinggroups2
     * 
     * @param \Netz\Edvisia\Domain\Model\Workinggroup $workinggroups2
     * @return void
     */
    public function setWorkinggroups2($workinggroups2)
    {
        $this->workinggroups2 = $workinggroups2;
    }

    /**
     * Returns the workinggroups3
     * 
     * @return \Netz\Edvisia\Domain\Model\Workinggroup $workinggroups3
     */
    public function getWorkinggroups3()
    {
        return $this->workinggroups3;
    }

    /**
     * Sets the workinggroups3
     * 
     * @param \Netz\Edvisia\Domain\Model\Workinggroup $workinggroups3
     * @return void
     */
    public function setWorkinggroups3($workinggroups3)
    {
        $this->workinggroups3 = $workinggroups3;
    }

    /**
     * Returns the workinggroups4
     * 
     * @return \Netz\Edvisia\Domain\Model\Workinggroup $workinggroups4
     */
    public function getWorkinggroups4()
    {
        return $this->workinggroups4;
    }

    /**
     * Sets the workinggroups4
     * 
     * @param \Netz\Edvisia\Domain\Model\Workinggroup $workinggroups4
     * @return void
     */
    public function setWorkinggroups4($workinggroups4)
    {
        $this->workinggroups4 = $workinggroups4;
    }


     /**
     * Returns the userid
     *
     * @return \Netz\Edvisia\Domain\Model\FrontendUser $userid
     */
    public function getUserid()
    {
        return $this->userid;
    }

    /**
     * Sets the userid
     *
     * @param \Netz\Edvisia\Domain\Model\FrontendUser $userid
     * @return void
     */
    public function setUserid($userid)
    {
        $this->userid = $userid;
    }


      /*
     * Adds a images
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $images
     * @return void
     */
    public function addImages(\TYPO3\CMS\Extbase\Domain\Model\FileReference $images)
    {
        $this->images->attach($images);
    }

    /**
     * Removes a images
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $imagesToRemove The Images to be removed
     * @return void
     */
    public function removeImages(\TYPO3\CMS\Extbase\Domain\Model\FileReference $imagesToRemove)
    {
        $this->images->detach($imagesToRemove);
    }

    /**
     * Returns the images
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference> $images
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Sets the images
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference> $images
     * @return void
     */
    public function setImages(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $images)
    {
        $this->images = $imags;
    }
}
