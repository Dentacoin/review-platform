/**
Rinkeby! TESTNET
 v.3
 * Dentacoin Review Platform contract created on July the xth, 2017 by Dentacoin B.V. in the Netherlands
 *
 * For terms and conditions visit https://dentacoin.com
 */

pragma solidity ^0.4.13;


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













contract Review is owned {

// Declaring Variables----------------------------------------------------------

  // Address of the DCN token: 0x08d32b0da63e2C3bcF8019c9c5d849d7a9d791e6
  dcnToken public tokenAddress;
  uint256 public count = 0;
  uint256 public secretCount = 0;
  uint256 public dcnAmount;
  uint256 public dcnAmountTrusted;
  bytes32 public hashedInSecret;
  bytes8[] public hashedSubmitSecrets;


  struct Reviews {
    // date of the review
    uint256 timeStamp;
    address writtenByAddress;
    bytes32 content;
  }
  mapping (uint256 => Reviews) public reviewID;

  mapping (address => bool) public dentistWhitelist;
  mapping (bytes32 => bool) public hashedInviteSecret;


  function Review() {
    tokenAddress = dcnToken(0x2Debb13BCF5526e0cF5E3A4E5049100E3F7c2AE5);
  }

// Events-----------------------------------------------------------------------
  event SubmitEvent(address indexed _from, address indexed _to, uint256 _value);

// Setter and Getter -----------------------------------------------------------

    function setDCNAmounts (uint256 _dcnAmount, uint256 _dcnAmountTrusted) onlyOwner {
      dcnAmount = _dcnAmount;
      dcnAmountTrusted = _dcnAmountTrusted;
    }

  //todo: return in functions setzen
    function setInviteSecret(string _secret) returns (bytes32 result){
      require (dentistWhitelist[msg.sender]);
      require (bytes(_secret).length != 0);
      // Store hashed invite secret in mapping
      hashedInviteSecret[keccak256(_secret)] = true;
      return keccak256(_secret);
    }

    function setDentistOnWhitelist (address _dentist) onlyOwner {
      require (_dentist != 0x0);
      dentistWhitelist[_dentist] = true;
    }
    function removeDentistFromWhitelist (address _dentist) onlyOwner {
      require (_dentist != 0x0);
      dentistWhitelist[_dentist] = false;
    }


    function addSubmitSecrets (bytes8[] _arrayOfHashedSecrets) onlyOwner {
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

    function getHashedSecrets() constant returns (bytes8[] hashedSecrets) {
      return hashedSubmitSecrets;
    }



// Main Functions --------------------------------------------------------------


  function () payable onlyOwner{}

  function submitReview(address _to, bytes32 _content, string _submitSecret, string _inviteSecret) returns (string trusted) {

    // Check if reviewer is not a dentist
    require (!dentistWhitelist[msg.sender]);

    //Check if review comes from actual user of reviews.dentacoin.com
    require (hashedSubmitSecrets[secretCount] == bytes8(keccak256(_submitSecret)));

    // Check if review contains any answers
    require (_content != 0x0);

    //Check if Dentist is whitelisted
    require (dentistWhitelist[_to]);


    //Store review details
    reviewID[count].timeStamp = now;
    reviewID[count].writtenByAddress = msg.sender;
    reviewID[count].content = _content;

    count++;

    if (secretCount < 99) {
      secretCount++;
    } else {
      secretCount = 0;
    }


    //Check if review is trusted by comparing hashed Secret from invite email with db
    if (hashedInviteSecret[keccak256(_inviteSecret)]) {
      // remove
      hashedInviteSecret[keccak256(_inviteSecret)] = false;
      tokenAddress.transfer(msg.sender, dcnAmountTrusted);
      SubmitEvent(this, msg.sender, dcnAmountTrusted);
      return "trusted";

    } else {
      tokenAddress.transfer(msg.sender, dcnAmount);
      SubmitEvent(this, msg.sender, dcnAmount);
      return "untrusted";
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




}
