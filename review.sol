pragma solidity ^0.4.11;



/**
 * Dentacoin extended ERC20 token contract created on February the 14th, 2017 by Dentacoin B.V. in the Netherlands
 *
 * For terms and conditions visit https://dentacoin.com
 */



contract owned {
    address public owner;

    function owned() {
        owner = msg.sender;
    }

    modifier onlyOwner {
        if (msg.sender != owner) throw;
        _;
    }

    function transferOwnership(address newOwner) onlyOwner {
        if (newOwner == 0x0) throw;
        owner = newOwner;
    }
}





/**
 * Overflow aware uint math functions.
 */
contract SafeMath {
  //internals

  function safeMul(uint a, uint b) internal returns (uint) {
    uint c = a * b;
    assert(a == 0 || c / a == b);
    return c;
  }

  function safeSub(uint a, uint b) internal returns (uint) {
    assert(b <= a);
    return a - b;
  }

  function safeAdd(uint a, uint b) internal returns (uint) {
    uint c = a + b;
    assert(c>=a && c>=b);
    return c;
  }

  function assert(bool assertion) internal {
    if (!assertion) throw;
  }
}


contract Review is owned, SafeMath {

/* Global variables */
  struct Feedback {
    uint256 timeStamp;

    string receiverFirstName;
    string receiverLastName;
    string receiverEmail;
    string receiverOfficeStreet;
    string receiverOfficePostal;
    string receiverOfficeCity;
    string receiverOfficeCountry;

    string senderAnswer;
  }
  mapping (uint256 => Feedback) feedbackID;

  function Review() {

  }







}
