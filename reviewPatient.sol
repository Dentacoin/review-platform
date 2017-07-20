/**
 * Dentacoin Review Platform contract created on July the xth, 2017 by Dentacoin B.V. in the Netherlands
 *
 * For terms and conditions visit https://dentacoin.com
 */



pragma solidity ^0.4.11;



 //Dentacoin token import
 contract dcnToken {
   function transfer(address, uint256) returns (bool) {  }
   function balanceOf(address) constant returns (uint256) {  }
 }


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
}


contract ReviewPatient is owned, SafeMath {

  // Address of the DCN token: 0x08d32b0da63e2C3bcF8019c9c5d849d7a9d791e6
  dcnToken public tokenAddress;
  uint256 i = 0;
  struct Patient {

  }
  struct Dentist {

  }



  struct Review {
    // date of the review
    uint256 timeStamp;
    address writtenByAddress;
    address writtenForAddress;
    uint256 writtenForID;
    string content;
  }

  mapping (uint256 => Review) reviewID;
  //if address != 0 then dentist is whitelisted
  mapping (address => uint256) dentistID;

  bytes32 submitSecret = "395b271ee1fd0bb891d35cd9391446d5af57051b079d0f53eb37d06a249958b3";






  function ReviewPatient() {
    tokenAddress = dcnToken(0x571280B600bBc3e2484F8AC80303F033b762048f);       // Define Dentacoin token address
  }


  function submitReview(address _to, uint256 _toID, string _content, bytes32 _secret) {
    require (keccak256(_secret) == submitSecret);
    reviewID[i].timeStamp = now;
    reviewID[i].writtenByAddress = msg.sender;
    reviewID[i].writtenForAddress = _to;
    reviewID[i].writtenForID = _toID;
    reviewID[i].content = _content;
    i++;
  }

/*
  function setDentistOnWhitelist(address _dentist) onlyOwner {
    dentistID[_dentist] =
  }

  function setSubmitSecret (bytes32 _HashedSecret) onlyOwner {
    submitSecret = _HashedSecret;
  }
*/

}
