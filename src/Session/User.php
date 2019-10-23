<?php

namespace Yoga\Session;

class User {

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string[]
     */
    private $permissions;

    public function getFullName() {
        if (!$this->firstName && !$this->lastName) {
            $fullName = \Yoga\Strings::service()->ellipsis($this->email, 70);
        } else {
            $fullName = $this->firstName;
            if ($this->lastName) {
                if ($fullName) {
                    $fullName .= ' ';
                }
                $fullName .= $this->lastName;
            }
        }
        return $fullName;
    }

    public function getFullEmail() {
        $result = $this->email;
        if ($this->firstName || $this->lastName) {
            $result = $this->getFullName() . ' <' . $result . '>';
        }
        return $result;
    }

    /**
     * @param string $id
     * @return User
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName) {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName() {
        return $this->firstName;
    }

    /**
     * @param string $lastName
     * @return User
     */
    public function setLastName($lastName) {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName() {
        return $this->lastName;
    }

    /**
     * @return \string[]
     */
    public function getPermissions() {
        return $this->permissions;
    }

    /**
     * @param \string[] $permissions
     * @return User
     */
    public function setPermissions(array $permissions) {
        $this->permissions = $permissions;
        return $this;
    }

}