



// To do:
// take in a dummy discord command as a string
// (example: "!submitmd Title="This is a document." Description="This is something sbout the document.")
// parse the string
export async function parseSubmission(discordMessageSubmission){
    let msg = discordMessageSubmission;
    const args = {};

    // If the string doesn't start with the command phrase then return.
    if (!msg.startsWith('!submitmd ')){
        console.log("Does not contain !submit.");
        return;
    }
    else{
        console.log("Valid !submit command.");
    }

    // Parse the command into seperate strings of the key and values provided by the client.
    // msg = "!submitmd Title='This is a document.' Description='This is something sbout the document.'"
    // input (after parsing) = ["Title='This is a document.'", "Description='This is something sbout the document.'"]
    let parsed_command_data_command_array = msg.slice('!submitmd '.length).match(/(\w+)=['"]([^'"]*?)['"]/g)
    //console.log(parsed_command_data_command_array);

    // Parse the array using regex + Array.prototype.reduce
    const parsed_command_data = parsed_command_data_command_array.reduce((obj, line) => {
    // ^(\w+)='(.+)'$ → capture the key (letters) and the value inside quotes
    const match = line.match(/^(\w+)='(.+)'$/);
    if (match) {
        const [, key, val] = match;
        // normalize key to lowercase (or whatever you prefer)
        obj[key.toLowerCase()] = val;
    }
    return obj;
    }, {});

    // Build a new document object that holds the parsed command data
    const newSubmissionJson = {
        id: "Today",     // or UUID
        title: parsed_command_data['title'],
        author: "none",
        description: parsed_command_data['description'],               
        submittedAt: new Date().toISOString()
    };
    console.log("User input: " + discordMessageSubmission)
    console.log(newSubmissionJson);

    // 3) Load, append, save
    
  //const store = await loadStore();
  //store.documents.push(newDoc);
  //await saveStore(store);

  //msg.reply(`✅ Submission received! You are document #${store.documents.length}.`);
    
}


// store into a new object (give an unique id)
// save to json file