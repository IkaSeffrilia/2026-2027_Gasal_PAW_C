<form action="processData.php" method="POST">
    <!-- Baris 18-21: Textbox Surname -->
    <label for="surname">Surname:</label><br>
    <input type="text" id="surname" name="surname"><br><br>

    <!-- Baris 22-25: Textbox Email address -->
    <label for="email">Email address:</label><br>
    <input type="text" id="email" name="email"><br><br>

    <!-- Baris 26-29: Password -->
    <label for="password">Password:</label><br>
    <input type="password" id="password" name="password"><br><br>

    <!-- Baris 30-33: Textarea Street Address -->
    <label for="street">Street Address:</label><br>
    <textarea id="street" name="street" rows="4" cols="30"></textarea><br><br>

    <!-- Baris 34-47: Dropdown State -->
    <label for="state">State:</label><br>
    <select id="state" name="state">
        <option value="">-- Pilih State --</option>
        <option value="New South Wales">New South Wales</option>
        <option value="Victoria">Victoria</option>
        <option value="Queensland">Queensland</option>
        <option value="Western Australia">Western Australia</option>
        <option value="South Australia">South Australia</option>
        <option value="Tasmania">Tasmania</option>
        <option value="Australian Capital Territory">Australian Capital Territory</option>
        <option value="Northern Territory">Northern Territory</option>
    </select><br><br>

    <!-- Baris 48-50: Hidden country -->
    <input type="hidden" name="country" value="Australia">

    <!-- Baris 51-55: Radio button Gender -->
    <label>Gender:</label><br>
    <input type="radio" id="male" name="gender" value="Male">
    <label for="male">Male</label>
    <input type="radio" id="female" name="gender" value="Female">
    <label for="female">Female</label><br><br>

    <!-- Baris 56-59: Checkbox Vegetarian? -->
    <input type="checkbox" id="vegetarian" name="vegetarian" value="Yes">
    <label for="vegetarian">Vegetarian?</label><br><br>

    <!-- Baris 60-63: Tombol Submit dan Reset -->
    <input type="submit" value="Submit">
    <input type="reset" value="Reset">
</form>