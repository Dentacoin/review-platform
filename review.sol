/**

Rinkeby TESTNET
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
          require (msg.sender == owner);
          _;
      }
      function transferOwnership(address newOwner) onlyOwner {
          require (newOwner != 0x0);
          owner = newOwner;
      }
  }

  /**
   * Overflow aware uint math functions.

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
 */



























contract Review is owned {

// Declaring Variables----------------------------------------------------------

  // Address of the DCN token: 0x08d32b0da63e2C3bcF8019c9c5d849d7a9d791e6
  dcnToken public tokenAddress;
  uint256 public count = 0;
  uint256 public dcnAmount;
  uint256 public dcnAmountTrusted;
  bytes32 public hashedInSecret;
  bytes32[] public hashedSubmitSecrets;


  struct Reviews {
    // date of the review
    uint256 timeStamp;
    address writtenByAddress;
    address writtenForAddress;
    uint256 writtenForID;
    bytes32 content;
    bool trusted;
  }

  mapping (uint256 => Reviews) public reviewID;
  mapping (address => bool) public dentistWhitelist;

  mapping (bytes32 => bool) public hashedInviteSecret;


  function Review() {
    tokenAddress = dcnToken(0x2Debb13BCF5526e0cF5E3A4E5049100E3F7c2AE5);       // Define Dentacoin token address Rinkeby
    // hash of secret "123" to submit reviews for testing !remove in main net version!
    //hashedSubmitSecrets[0x64e604787cbf194841e7b68d7cd28786f6c9a0a3ab9f8b0a0e87cb4387ab0107] = true;
  }

// Setter and Getter -----------------------------------------------------------

    function setDCNAmounts (uint256 _dcnAmount, uint256 _dcnAmountTrusted) onlyOwner {
      dcnAmount = _dcnAmount;
      dcnAmountTrusted = _dcnAmountTrusted;
    }

  //todo: return in functions setzen
    function setInviteSecret(bytes32 _secret) {
      require (dentistWhitelist[msg.sender]);
      require (_secret != 0x0);
      // Store hashed invite secret in mapping
      hashedInviteSecret[keccak256(_secret)] = true;
    }

    function setDentistOnWhitelist (address _dentist) onlyOwner {
      require (_dentist != 0x0);
      dentistWhitelist[_dentist] = true;
    }

    function addSubmitSecrets (bytes32[] _arrayOfHashedSecrets) onlyOwner {
      for (uint i = 0; i < _arrayOfHashedSecrets.length; i++) {
            hashedSubmitSecrets.push(_arrayOfHashedSecrets[i]);
        }
    }


    function getContractBalance() constant returns (uint256 balance) {
      return tokenAddress.balanceOf(this);
    }

    function getReviewCount() constant returns (uint256 reviewCount) {
      return count;
    }

    function getHashedSecrets() constant returns (bytes32[] hashedSecrets) {
      return hashedSubmitSecrets;
    }



// Main Functions --------------------------------------------------------------


  function () payable onlyOwner{}

  function submitReview(address _to, uint256 _toID, bytes32 _content, bytes16 _submitSecret, bytes32 _inviteSecret) returns (bool success) {

    // Check if reviewer is not a dentist
    require (!dentistWhitelist[msg.sender]);

    //Check if review comes from actual user of reviews.dentacoin.com
    require (hashedSubmitSecrets[0] == keccak256(_submitSecret));               //Testnet
    //require (hashedSubmitSecrets[count] == keccak256(_submitSecret));

    // Check if review contains any answers
    require (_content != 0x0);

    //Check if Dentist is whitelisted
    require (dentistWhitelist[_to]);

    //Remove secret from list after using it once !uncomment in main net version!
    //hashedSubmitSecrets[keccak256(_secret)] = false;

    //Store review details
    reviewID[count].timeStamp = now;
    reviewID[count].writtenByAddress = msg.sender;
    reviewID[count].writtenForAddress = _to;
    reviewID[count].writtenForID = _toID;
    reviewID[count].content = _content;

    //Check if review is trusted by comparing hashed Secret from invite email with db
    if (hashedInviteSecret[keccak256(_inviteSecret)]) {
      reviewID[count].trusted = true;
      // remove
      hashedInviteSecret[keccak256(_inviteSecret)] = false;
      tokenAddress.transfer(msg.sender, dcnAmountTrusted);
      count++;
      return true;

    } else {
      tokenAddress.transfer(msg.sender, dcnAmount);
      count++;
      return true;
    }

  }



// Admin section ---------------------------------------------------------------

  function refundToOwner () onlyOwner {
      if (tokenAddress.balanceOf(this) > 0) {
        tokenAddress.transfer(msg.sender, tokenAddress.balanceOf(this));
      }
      if (this.balance > 0) {
        msg.sender.transfer(this.balance);
      }
  }











  //Dentist section

    //if address true then dentist is whitelisted

/*
    struct Dentist {

    }

    function updateAddress(uint256 _id, address _address) {
      require (dentistWhitelist[_id])
    }

*/











}
